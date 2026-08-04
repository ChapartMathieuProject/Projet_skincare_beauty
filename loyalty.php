<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'public/includes/db.php';
require_once 'public/includes/Mailer.php';

$customerStatement = $pdo->prepare('SELECT * FROM customers WHERE user_id = :userId');
$customerStatement->execute(['userId' => $_SESSION['user_id']]);
$customer = $customerStatement->fetch();

if (!$customer) {
    header('Location: users.php');
    exit;
}

$customerId = (int) $customer['customer_id_account'];

$loyaltyService = new LoyaltyService(
    new LoyaltyPointDAO($pdo),
    new LoyaltyTierDAO($pdo),
    new LoyaltyVoucherDAO($pdo),
    $mailer,
    $pdo
);

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'convert') {
    $pointsToConvert = (int) ($_POST['points'] ?? 0);

    try {
        $voucher = $loyaltyService->convertPointsToVoucher($customerId, $pointsToConvert);
        $_SESSION['loyalty_voucher_created'] = $voucher->getCode();

        header('Location: loyalty.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $errorMessage = $e->getMessage();
    } catch (Throwable $e) {
        error_log('Fidelite : echec conversion client ' . $customerId . ' - ' . $e->getMessage());
        $errorMessage = 'Une erreur est survenue, merci de reessayer.';
    }
}

$balance          = $loyaltyService->getBalance($customerId);
$currentTier      = $loyaltyService->getTier($customerId);
$pointsToNextTier = $loyaltyService->getPointsToNextTier($customerId);
$convertibleValue = $loyaltyService->getConvertibleValue($customerId);
$history          = $loyaltyService->getHistory($customerId);
$vouchers         = $loyaltyService->getVouchers($customerId);

require_once 'public/includes/header.php';
?>

<main class="profile-main">
    <div class="container">

        <nav aria-label="Fil d'Ariane" class="profile-breadcrumb">
            <a href="/users.php">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Mon profil
            </a>
        </nav>

        <h1 class="profile-page-title">
            <i class="fa-solid fa-gift" aria-hidden="true"></i>
            Mon programme de fidélité
        </h1>

        <?php if (!empty($_SESSION['loyalty_voucher_created'])): ?>
            <div class="alert alert-success">
                Votre bon de réduction
                <strong><?= htmlspecialchars($_SESSION['loyalty_voucher_created']) ?></strong>
                a bien été créé.
            </div>
            <?php unset($_SESSION['loyalty_voucher_created']); ?>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div class="loyalty-grid">

            <article class="loyalty-card loyalty-card--balance">
                <div class="loyalty-card__header">
                    <i class="fa-solid fa-coins" aria-hidden="true"></i>
                    <h2 class="loyalty-card__title">Mon solde</h2>
                </div>
                <div class="loyalty-card__body">
                    <p class="loyalty-balance"><?= $balance ?> <span>points</span></p>

                    <?php if ($currentTier): ?>
                        <span class="loyalty-tier">
                            <i class="fa-solid fa-award" aria-hidden="true"></i>
                            Palier <?= htmlspecialchars($currentTier->getName()) ?>
                        </span>
                        <p class="loyalty-hint">
                            Vous bénéficiez de <?= htmlspecialchars($currentTier->getAdvantagesLabel()) ?>.
                        </p>
                    <?php endif; ?>

                    <?php if ($pointsToNextTier !== null): ?>
                        <?php
                        $tierFloor = $currentTier ? $currentTier->getMinPoints() : 0;
                        $target    = $tierFloor + $pointsToNextTier;
                        $progress  = $target > 0 ? (1 - $pointsToNextTier / $target) * 100 : 0;
                        ?>
                        <div class="loyalty-progress">
                            <div class="loyalty-progress__bar" style="width: <?= round($progress) ?>%"></div>
                        </div>
                        <p class="loyalty-hint">
                            Encore <strong><?= $pointsToNextTier ?> points</strong> pour le palier suivant.
                        </p>
                    <?php else: ?>
                        <p class="loyalty-hint">Vous avez atteint le palier maximum. Merci de votre fidélité.</p>
                    <?php endif; ?>
                </div>
            </article>

            <article class="loyalty-card">
                <div class="loyalty-card__header">
                    <i class="fa-solid fa-ticket" aria-hidden="true"></i>
                    <h2 class="loyalty-card__title">Convertir mes points</h2>
                </div>
                <div class="loyalty-card__body">
                    <p class="loyalty-empty">
                        <?= LoyaltyService::POINTS_PER_VOUCHER ?> points =
                        <?= number_format(LoyaltyService::VOUCHER_VALUE, 2, ',', ' ') ?> € de réduction.
                    </p>

                    <?php if ($convertibleValue > 0): ?>
                        <form method="post" action="loyalty.php" class="loyalty-convert-form">
                            <input type="hidden" name="action" value="convert">
                            <div>
                                <label for="points">Nombre de points à convertir</label>
                                <select name="points" id="points" class="loyalty-select">
                                    <?php
                                    $maxSteps = intdiv($balance, LoyaltyService::POINTS_PER_VOUCHER);
                                    for ($step = 1; $step <= $maxSteps; $step++):
                                        $stepPoints = $step * LoyaltyService::POINTS_PER_VOUCHER;
                                        $stepValue  = $step * LoyaltyService::VOUCHER_VALUE;
                                    ?>
                                        <option value="<?= $stepPoints ?>">
                                            <?= $stepPoints ?> points — <?= number_format($stepValue, 2, ',', ' ') ?> €
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-rose">Convertir</button>
                        </form>
                    <?php else: ?>
                        <p class="loyalty-hint">
                            Il vous manque
                            <strong><?= LoyaltyService::POINTS_PER_VOUCHER - ($balance % LoyaltyService::POINTS_PER_VOUCHER) ?> points</strong>
                            pour obtenir votre premier bon.
                        </p>
                    <?php endif; ?>
                </div>
            </article>

            <article class="loyalty-card">
                <div class="loyalty-card__header">
                    <i class="fa-solid fa-tags" aria-hidden="true"></i>
                    <h2 class="loyalty-card__title">Mes bons de réduction</h2>
                </div>

                <?php if (empty($vouchers)): ?>
                    <div class="loyalty-card__body">
                        <p class="loyalty-empty">Vous n'avez pas encore de bon de réduction.</p>
                    </div>
                <?php else: ?>
                    <div class="loyalty-card__body loyalty-card__body--flush">
                        <table class="loyalty-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Montant</th>
                                    <th>Expire le</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vouchers as $voucher): ?>
                                    <tr>
                                        <td class="loyalty-code"><?= htmlspecialchars($voucher->getCode()) ?></td>
                                        <td><?= number_format($voucher->getAmount(), 2, ',', ' ') ?> €</td>
                                        <td><?= date('d/m/Y', strtotime($voucher->getExpiresAt())) ?></td>
                                        <td>
                                            <?php if ($voucher->isUsed()): ?>
                                                <span class="loyalty-badge loyalty-badge--used">Utilisé</span>
                                            <?php elseif ($voucher->isExpired()): ?>
                                                <span class="loyalty-badge loyalty-badge--expired">Expiré</span>
                                            <?php else: ?>
                                                <span class="loyalty-badge loyalty-badge--available">Disponible</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </article>

            <article class="loyalty-card">
                <div class="loyalty-card__header">
                    <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                    <h2 class="loyalty-card__title">Historique de mes points</h2>
                </div>

                <?php if (empty($history)): ?>
                    <div class="loyalty-card__body">
                        <p class="loyalty-empty">Aucun mouvement pour le moment.</p>
                    </div>
                <?php else: ?>
                    <div class="loyalty-card__body loyalty-card__body--flush">
                        <table class="loyalty-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Libellé</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $movement): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($movement->getCreatedAt())) ?></td>
                                        <td><?= htmlspecialchars($movement->getLabel()) ?></td>
                                        <td class="<?= $movement->isCredit() ? 'loyalty-amount--credit' : 'loyalty-amount--debit' ?>">
                                            <?= $movement->getFormattedAmount() ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </article>

        </div>

    </div>
</main>

<?php require_once 'public/includes/footer.php'; ?>