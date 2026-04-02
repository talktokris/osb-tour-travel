<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$result = $mysqli->query('SELECT agent_id, agent_name, agent_country, agent_city FROM agent ORDER BY agent_name LIMIT 50');
?>

<?php
$currentPage = $_GET['page'] ?? 'home';
?>

<div class="flex gap-6 w-full">
    <!-- Left sidebar -->
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/../../includes/sidebar.php'; ?>
    </aside>

    <!-- Main content -->
    <main class="flex-1">
        <div class="space-y-4">
            <h2 class="text-2xl font-semibold text-base-content">Agents</h2>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <p class="text-base-content/70 text-sm mb-4">Master list of travel agents (first 50 by name).</p>
                    <?php if ($result === false): ?>
                        <div role="alert" class="alert alert-error">
                            <span>Could not load agents. Check the database connection.</span>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto rounded-box border border-base-300">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Country</th>
                                        <th>City</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="font-mono text-sm"><?= h((string) $row['agent_id']) ?></td>
                                            <td class="font-medium"><?= h($row['agent_name']) ?></td>
                                            <td><?= h($row['agent_country']) ?></td>
                                            <td><?= h($row['agent_city']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
