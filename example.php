<?php
/**
 * MarkenFlux SMM Social API - Offizielle Verwaltungs- und Testoberfläche
 * ALL RIGHTS RESERVED. (C) MARKENFLUX.DE
 */

$responseResult = null;
$errorMsg = null;
$defaultApiUrl = 'https://panel.markenflux.de/api/v2'; // API BASE URL
$defaultApiKey = ''; // YOUR SECRET API KEY

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiUrl   = trim($_POST['api_url'] ?? $defaultApiUrl);
    $apiKey   = trim($_POST['api_key'] ?? '');
    $action   = trim($_POST['action'] ?? '');

    if (empty($apiUrl) || empty($apiKey) || empty($action)) {
        $errorMsg = "Bitte füllen Sie den API-Schlüssel und die Aktion vollständig aus.";
    } else {
        $postData = [
            'key'    => $apiKey,
            'action' => $action
        ];

        // Zusätzliche Parameter basierend auf der Aktion
        if ($action === 'add') {
            $postData['service']  = intval($_POST['service'] ?? 0);
            $postData['link']     = trim($_POST['link'] ?? '');
            $postData['quantity'] = intval($_POST['quantity'] ?? 0);
        } elseif ($action === 'status' || $action === 'refill' || $action === 'cancel') {
            $orderInput = trim($_POST['order_id'] ?? '');
            if (strpos($orderInput, ',') !== false) {
                $postData['orders'] = $orderInput;
            } else {
                $postData['order'] = intval($orderInput);
            }
        }

        // cURL-Anfrage ausführen
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, rtrim($apiUrl, '/'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $apiOutput = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $errorMsg = "cURL-Verbindungsfehler: " . curl_error($ch);
        } else {
            $decoded = json_decode($apiOutput, true);
            $responseResult = $decoded !== null ? $decoded : $apiOutput;
        }
        curl_close($ch);
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarkenFlux Social - API Verwaltungspanel</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-warning">MarkenFlux Social API</h2>
                    <p class="text-secondary">panel.markenflux.de v2 Integrations- und Steuerungspanel</p>
                </div>

                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Fehler:</strong> <?= htmlspecialchars($errorMsg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
                    </div>
                <?php endif; ?>

                <div class="card bg-secondary bg-opacity-10 border-secondary shadow-sm mb-4">
                    <div class="card-body p-4">
                        <form method="POST" action="">
                            
                            <!-- API Endpoint & Key -->
                            <div class="mb-3">
                                <label for="api_url" class="form-label fw-semibold text-light">API Endpunkt URL</label>
                                <input type="url" class="form-control bg-dark text-light border-secondary" id="api_url" name="api_url" 
                                       value="<?= htmlspecialchars($_POST['api_url'] ?? $defaultApiUrl) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="api_key" class="form-label fw-semibold text-light">MarkenFlux API-Schlüssel</label>
                                <input type="password" class="form-control bg-dark text-light border-secondary" id="api_key" name="api_key" 
                                       placeholder="Geben Sie Ihren API-Schlüssel ein..." 
                                       value="<?= htmlspecialchars($_POST['api_key'] ?? '') ?>" required>
                            </div>

                            <!-- Action Selection -->
                            <div class="mb-3">
                                <label for="action" class="form-label fw-semibold text-light">Aktion auswählen</label>
                                <select class="form-select bg-dark text-light border-secondary" id="action" name="action" required onchange="toggleFormFields()">
                                    <option value="" selected disabled>-- Bitte Aktion wählen --</option>
                                    <option value="balance" <?= (($_POST['action'] ?? '') === 'balance') ? 'selected' : '' ?>>Guthaben abfragen (Balance)</option>
                                    <option value="services" <?= (($_POST['action'] ?? '') === 'services') ? 'selected' : '' ?>>Dienste auflisten (Services)</option>
                                    <option value="add" <?= (($_POST['action'] ?? '') === 'add') ? 'selected' : '' ?>>Bestellung aufgeben (Add Order)</option>
                                    <option value="status" <?= (($_POST['action'] ?? '') === 'status') ? 'selected' : '' ?>>Bestellstatus abfragen (Status / Mehrfach)</option>
                                    <option value="refill" <?= (($_POST['action'] ?? '') === 'refill') ? 'selected' : '' ?>>Auffüllung anfordern (Refill)</option>
                                    <option value="cancel" <?= (($_POST['action'] ?? '') === 'cancel') ? 'selected' : '' ?>>Bestellung stornieren (Cancel)</option>
                                </select>
                            </div>

                            <!-- Dynamic Fields for Add Order -->
                            <div id="addOrderFields" class="border border-secondary p-3 rounded mb-3 bg-dark d-none">
                                <h6 class="text-warning mb-3">Bestellparameter</h6>
                                <div class="mb-3">
                                    <label for="service" class="form-label text-light">Dienst-ID (Service ID)</label>
                                    <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" id="service" name="service" value="<?= htmlspecialchars($_POST['service'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="link" class="form-label text-light">Ziel-Link (Target Link / URL)</label>
                                    <input type="text" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" id="link" name="link" placeholder="https://..." value="<?= htmlspecialchars($_POST['link'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="quantity" class="form-label text-light">Menge (Quantity)</label>
                                    <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" id="quantity" name="quantity" value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Dynamic Fields for Order ID / IDs (Status, Refill, Cancel) -->
                            <div id="orderIdFields" class="border border-secondary p-3 rounded mb-3 bg-dark d-none">
                                <h6 class="text-warning mb-3">Bestellnummer(n) Parameter</h6>
                                <div class="mb-3">
                                    <label for="order_id" class="form-label text-light">Bestell-ID oder mehrere IDs (durch Komma getrennt)</label>
                                    <input type="text" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" id="order_id" name="order_id" placeholder="Z. B. 142563 oder 142563,142564" value="<?= htmlspecialchars($_POST['order_id'] ?? '') ?>">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-warning w-100 py-2 fw-semibold text-dark">Anfrage senden</button>
                        </form>
                    </div>
                </div>

                <!-- Response Box -->
                <?php if ($responseResult !== null): ?>
                    <div class="card bg-secondary bg-opacity-10 border-secondary shadow-sm">
                        <div class="card-header bg-black text-warning d-flex justify-content-between align-items-center border-secondary">
                            <span class="fw-semibold">MarkenFlux API-Antwort (Response)</span>
                            <span class="badge bg-warning text-dark">JSON Ausgabe</span>
                        </div>
                        <div class="card-body bg-black">
                            <pre class="text-light mb-0" style="max-height: 400px; overflow-y: auto;"><code><?php 
                                if (is_array($responseResult)) {
                                    echo htmlspecialchars(json_encode($responseResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                } else {
                                    echo htmlspecialchars($responseResult);
                                }
                            ?></code></pre>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleFormFields() {
            const action = document.getElementById('action').value;
            const addFields = document.getElementById('addOrderFields');
            const orderFields = document.getElementById('orderIdFields');

            addFields.classList.add('d-none');
            orderFields.classList.add('d-none');

            if (action === 'add') {
                addFields.classList.remove('d-none');
            } else if (action === 'status' || action === 'refill' || action === 'cancel') {
                orderFields.classList.remove('d-none');
            }
        }

        window.onload = function() {
            toggleFormFields();
        };
    </script>
  <!-- ALL RIGHTS RESERVED (C) MARKENFLUX.DE -->
</body>
</html>
