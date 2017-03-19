<?php $this->beginContent('@users/views/layouts/auth_response.php'); ?>
    <?php foreach ($data as $key => $value): ?>
        "<?= $key ?>": "<?= $value ?>",
    <?php endforeach; ?>

    "auth_token": "<?= $token ?>",
    "uid": "<?= $uid ?>",
    "message": "deliverCredentials",
    "client_id": "<?= $client_id ?>",
    "expiry": "<?= $expiry ?>"
<?php $this->endContent(); ?>
