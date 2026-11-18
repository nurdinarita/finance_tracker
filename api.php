<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];


function fetchYahooPrice(string $symbol): ?float {
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/" . urlencode($symbol) . "?interval=1d&range=1d";
    $resp = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $out = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200 && $out) $resp = $out;
    }

    if (!$resp && ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 8,
                'header' => "User-Agent: Mozilla/5.0\r\nAccept: application/json\r\n"
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        $resp = @file_get_contents($url, false, $ctx);
    }

    if (!$resp) return null;
    $json = json_decode($resp, true);
    $price = $json['chart']['result'][0]['meta']['regularMarketPrice'] ?? null;
    return is_numeric($price) ? (float) $price : null;
}

function applyAccountBalance(PDO $pdo, array $tx, int $sign = 1): void {
    $amt = (float) $tx['amount'] * $sign;
    $accId = (int) $tx['account_id'];
    $toId = !empty($tx['transfer_to_id']) ? (int) $tx['transfer_to_id'] : null;
    $note = (string) ($tx['note'] ?? '');
    $txId = $tx['id'] ?? null;

    if ($tx['type'] === 'expense') {
        $pdo->prepare("UPDATE account SET balance = balance - ? WHERE id = ?")->execute([$amt, $accId]);
        $pdo->prepare("INSERT INTO account_change_log (account_id, tx_id, content, change_amount) VALUES (?, ?, ?, ?)")
            ->execute([$accId, $txId, $note ?: 'Pengeluaran', -$amt]);
    } elseif ($tx['type'] === 'income') {
        $pdo->prepare("UPDATE account SET balance = balance + ? WHERE id = ?")->execute([$amt, $accId]);
        $pdo->prepare("INSERT INTO account_change_log (account_id, tx_id, content, change_amount) VALUES (?, ?, ?, ?)")
            ->execute([$accId, $txId, $note ?: 'Pemasukan', $amt]);
    } elseif ($tx['type'] === 'transfer' && $toId) {
        $pdo->prepare("UPDATE account SET balance = balance - ? WHERE id = ?")->execute([$amt, $accId]);
        $pdo->prepare("UPDATE account SET balance = balance + ? WHERE id = ?")->execute([$amt, $toId]);
        $pdo->prepare("INSERT INTO account_change_log (account_id, tx_id, content, change_amount) VALUES (?, ?, ?, ?)")
            ->execute([$accId, $txId, $note ?: 'Transfer keluar', -$amt]);
        $pdo->prepare("INSERT INTO account_change_log (account_id, tx_id, content, change_amount) VALUES (?, ?, ?, ?)")
            ->execute([$toId, $txId, $note ?: 'Transfer masuk', $amt]);
    }
}

try {
    $pdo = getDB();

    if ($action === 'bootstrap') {
        $settings = $pdo->query("SELECT * FROM app_settings WHERE id = 1")->fetch();
        $books = $pdo->query("SELECT * FROM account_book ORDER BY id")->fetchAll();
        $accounts = $pdo->query("SELECT * FROM account WHERE disuse = 0 ORDER BY sort_order ASC, account_type ASC, id ASC")->fetchAll();
        $members = $pdo->query("SELECT * FROM member ORDER BY id")->fetchAll();
        $tags = $pdo->query("SELECT * FROM tag WHERE status = 0 ORDER BY is_pinned DESC, id")->fetchAll();
        $budgets = $pdo->query("SELECT * FROM budget ORDER BY id DESC")->fetchAll();
        $goals = $pdo->query("SELECT g.*, COALESCE(SUM(gr.amount), 0) AS saved_amount FROM goal g LEFT JOIN goal_record gr ON g.id = gr.goal_id GROUP BY g.id ORDER BY g.id DESC")->fetchAll();
        $investments = $pdo->query("SELECT * FROM investments ORDER BY id DESC")->fetchAll();

        $rows = $pdo->query("SELECT * FROM transactions ORDER BY tx_date DESC, tx_time DESC")->fetchAll();
        $tagMap = [];
        foreach ($pdo->query("SELECT bt.tx_id, t.id, t.name, t.color FROM bill_tag bt JOIN tag t ON bt.tag_id = t.id")->fetchAll() as $tr) {
            $tagMap[$tr['tx_id']][] = ['id' => (int)$tr['id'], 'name' => $tr['name'], 'color' => $tr['color']];
        }

        $transactions = array_map(function ($r) use ($tagMap) {
            return [
                'id' => $r['id'],
                'bookId' => (int) $r['book_id'],
                'type' => $r['type'],
                'categoryId' => $r['category_id'],
                'accountId' => (int) $r['account_id'],
                'transferToId' => $r['transfer_to_id'] ? (int) $r['transfer_to_id'] : null,
                'memberId' => (int) $r['member_id'],
                'amount' => (float) $r['amount'],
                'note' => $r['note'] ?? '',
                'date' => $r['tx_date'],
                'time' => $r['tx_time'],
                'location' => $r['location_name'] ? ['name' => $r['location_name'], 'coords' => $r['location_coords'] ?? ''] : null,
                'tags' => $tagMap[$r['id']] ?? [],
            ];
        }, $rows);

        echo json_encode([
            'ok' => true,
            'settings' => [
                'activeBookId' => (int) $settings['active_book_id'],
                'theme' => $settings['theme'],
                'lang' => $settings['lang'],
                'currency' => $settings['currency'],
                'widgetStyle' => $settings['widget_style'],
                'securityEnabled' => (bool) $settings['security_enabled'],
                'pinCode' => $settings['pin_code'],
            ],
            'books' => $books,
            'accounts' => array_map(fn($a) => [...$a, 'id' => (int)$a['id'], 'account_type' => (int)$a['account_type'], 'balance' => (float)$a['balance']], $accounts),
            'members' => $members,
            'tags' => $tags,
            'budgets' => $budgets,
            'goals' => $goals,
            'investments' => array_map(fn($inv) => [...$inv, 'id'=>(int)$inv['id'], 'quantity'=>(float)$inv['quantity'], 'buy_price_per_unit'=>(float)$inv['buy_price_per_unit'], 'invested_amount'=>(float)$inv['invested_amount'], 'current_price_per_unit'=>(float)$inv['current_price_per_unit']], $investments),
            'transactions' => $transactions,
        ]);
        exit;
    }

    if ($action === 'save_transaction') {
        $pdo->beginTransaction();
        $id = $input['id'] ?? ('tx_' . round(microtime(true) * 1000));
        $old = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
        $old->execute([$id]);
        if ($oldRow = $old->fetch()) {
            applyAccountBalance($pdo, $oldRow, -1);
        }

        $txData = [
            'id' => $id,
            'book_id' => (int) ($input['bookId'] ?? 1),
            'type' => in_array($input['type'] ?? '', ['expense', 'income', 'transfer']) ? $input['type'] : 'expense',
            'category_id' => $input['categoryId'] ?? 'other_exp',
            'account_id' => (int) ($input['accountId'] ?? 1),
            'transfer_to_id' => !empty($input['transferToId']) ? (int) $input['transferToId'] : null,
            'member_id' => (int) ($input['memberId'] ?? 1),
            'amount' => (float) ($input['amount'] ?? 0),
            'note' => (string) ($input['note'] ?? ''),
            'tx_date' => $input['date'] ?? date('Y-m-d'),
            'tx_time' => $input['time'] ?? date('H:i'),
            'location_name' => $input['location']['name'] ?? null,
            'location_coords' => $input['location']['coords'] ?? null,
        ];

        $pdo->prepare("
            INSERT INTO transactions (id, book_id, type, category_id, account_id, transfer_to_id, member_id, amount, note, tx_date, tx_time, location_name, location_coords)
            VALUES (:id, :book_id, :type, :category_id, :account_id, :transfer_to_id, :member_id, :amount, :note, :tx_date, :tx_time, :location_name, :location_coords)
            ON DUPLICATE KEY UPDATE
              book_id=VALUES(book_id), type=VALUES(type), category_id=VALUES(category_id), account_id=VALUES(account_id),
              transfer_to_id=VALUES(transfer_to_id), member_id=VALUES(member_id), amount=VALUES(amount), note=VALUES(note),
              tx_date=VALUES(tx_date), tx_time=VALUES(tx_time), location_name=VALUES(location_name), location_coords=VALUES(location_coords)
        ")->execute($txData);

        applyAccountBalance($pdo, $txData, 1);

        $pdo->prepare("DELETE FROM bill_tag WHERE tx_id = ?")->execute([$id]);
        $insTag = $pdo->prepare("INSERT INTO bill_tag (tx_id, tag_id) VALUES (?, ?)");
        foreach ($input['tagIds'] ?? [] as $tid) {
            $insTag->execute([$id, (int) $tid]);
        }

        $pdo->commit();
        echo json_encode(['ok' => true, 'id' => $id]);
        exit;
    }

    if ($action === 'delete_transaction') {
        $pdo->beginTransaction();
        $id = $input['id'] ?? '';
        $old = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
        $old->execute([$id]);
        if ($oldRow = $old->fetch()) {
            applyAccountBalance($pdo, $oldRow, -1);
            $pdo->prepare("DELETE FROM transactions WHERE id = ?")->execute([$id]);
        }
        $pdo->commit();
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'save_settings') {
        $pdo->prepare("UPDATE app_settings SET active_book_id=?, theme=?, lang=?, currency=?, widget_style=?, security_enabled=?, pin_code=? WHERE id=1")
            ->execute([
                (int) ($input['activeBookId'] ?? 1),
                $input['theme'] ?? 'theme_blue',
                $input['lang'] ?? 'id',
                $input['currency'] ?? 'IDR',
                $input['widgetStyle'] ?? 'compact',
                !empty($input['securityEnabled']) ? 1 : 0,
                $input['pinCode'] ?? '1234',
            ]);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'save_account') {
        $pdo->prepare("INSERT INTO account (account_type, name, balance, note) VALUES (?, ?, ?, ?)")
            ->execute([(int)($input['account_type'] ?? 1), $input['name'], (float)($input['balance'] ?? 0), $input['note'] ?? '']);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'save_account_order') {
        $order = $input['order'] ?? [];
        $stmt = $pdo->prepare("UPDATE account SET sort_order = ? WHERE account_type = ?");
        foreach ($order as $idx => $accType) {
            $stmt->execute([$idx + 1, (int) $accType]);
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'delete_account') {
        $id = (int) ($input['id'] ?? 0);
        $pdo->prepare("UPDATE account SET disuse = 1 WHERE id = ?")->execute([$id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'account_logs') {
        $st = $pdo->prepare("SELECT * FROM account_change_log WHERE account_id = ? ORDER BY id DESC LIMIT 30");
        $st->execute([(int)($_GET['id'] ?? 0)]);
        echo json_encode(['ok' => true, 'logs' => $st->fetchAll()]);
        exit;
    }

    if ($action === 'save_budget') {
        $pdo->prepare("INSERT INTO budget (book_id, name, category_id, amount, note) VALUES (?, ?, ?, ?, ?)")
            ->execute([(int)($input['bookId'] ?? 1), $input['name'], $input['categoryId'] ?: null, (float)($input['amount'] ?? 0), $input['note'] ?? '']);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'save_goal') {
        $pdo->prepare("INSERT INTO goal (book_id, name, target_amount, goal_type, note) VALUES (?, ?, ?, ?, ?)")
            ->execute([(int)($input['bookId'] ?? 1), $input['name'], (float)($input['target_amount'] ?? 0), $input['goal_type'] ?? 'flexible', $input['note'] ?? '']);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'deposit_goal') {
        $pdo->prepare("INSERT INTO goal_record (goal_id, amount, note) VALUES (?, ?, ?)")
            ->execute([(int)$input['goal_id'], (float)$input['amount'], $input['note'] ?? 'Setoran']);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'save_tag') {
        $pdo->prepare("INSERT INTO tag (book_id, name, color) VALUES (?, ?, ?)")
            ->execute([(int)($input['bookId'] ?? 1), ltrim((string)$input['name'], '#'), $input['color'] ?? '#2563eb']);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'save_book') {
        $pdo->prepare("INSERT INTO account_book (name, note) VALUES (?, ?)")
            ->execute([$input['name'], $input['note'] ?? '']);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'clear_all') {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM bill_tag");
        $pdo->exec("DELETE FROM transactions");
        $pdo->exec("DELETE FROM account_change_log");
        $pdo->exec("UPDATE account SET balance = 0");
        $pdo->commit();
        echo json_encode(['ok' => true]);
        exit;
    }


    if ($action === 'save_investment') {
        $id = !empty($input['id']) ? (int) $input['id'] : null;
        $qty = (float) ($input['quantity'] ?? 0);
        $invested = (float) ($input['invested_amount'] ?? 0);
        $buyPrice = $qty > 0 ? ($invested / $qty) : (float) ($input['buy_price_per_unit'] ?? 0);
        $curPrice = !empty($input['current_price_per_unit']) ? (float) $input['current_price_per_unit'] : $buyPrice;
        $data = [
            (int) ($input['bookId'] ?? 1),
            $input['asset_type'] ?? 'stock',
            $input['name'] ?? '',
            strtoupper(trim((string) ($input['ticker'] ?? ''))),
            $input['unit_label'] ?? 'unit',
            $qty,
            $buyPrice,
            $invested,
            $curPrice,
            $input['note'] ?? '',
        ];
        if ($id) {
            $data[] = $id;
            $pdo->prepare("UPDATE investments SET book_id=?, asset_type=?, name=?, ticker=?, unit_label=?, quantity=?, buy_price_per_unit=?, invested_amount=?, current_price_per_unit=?, note=? WHERE id=?")->execute($data);
        } else {
            $pdo->prepare("INSERT INTO investments (book_id, asset_type, name, ticker, unit_label, quantity, buy_price_per_unit, invested_amount, current_price_per_unit, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute($data);
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'delete_investment') {
        $pdo->prepare("DELETE FROM investments WHERE id = ?")->execute([(int) ($input['id'] ?? 0)]);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'fetch_live_prices') {
        $rows = $pdo->query("SELECT * FROM investments")->fetchAll();
        $usdIdr = fetchYahooPrice('IDR=X') ?? 15850.0;
        $goldUsdOz = null;
        $updated = 0;

        foreach ($rows as $r) {
            $ticker = strtoupper(trim((string) $r['ticker']));
            $newPrice = null;

            if ($r['asset_type'] === 'gold') {
                if ($goldUsdOz === null) $goldUsdOz = fetchYahooPrice('GC=F');
                if ($goldUsdOz !== null) {
                    $newPrice = ($goldUsdOz / 31.1034768) * $usdIdr; // Harga per gram IDR
                }
            } elseif ($r['asset_type'] === 'stock' && $ticker !== '') {
                $p = fetchYahooPrice($ticker);
                if ($p !== null) {
                    $newPrice = str_ends_with($ticker, '.JK') ? $p : ($p * $usdIdr);
                }
            } elseif ($r['asset_type'] === 'crypto' && $ticker !== '') {
                $sym = str_contains($ticker, '-') ? $ticker : ($ticker . '-USD');
                $p = fetchYahooPrice($sym);
                if ($p !== null) {
                    $newPrice = $p * $usdIdr;
                }
            }

            if ($newPrice !== null && $newPrice > 0) {
                $pdo->prepare("UPDATE investments SET current_price_per_unit = ?, last_price_update = NOW() WHERE id = ?")
                    ->execute([round($newPrice, 2), $r['id']]);
                $updated++;
            }
        }
        echo json_encode(['ok' => true, 'updated' => $updated, 'usdIdr' => $usdIdr]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Action tidak valid']);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
