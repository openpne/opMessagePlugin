<?php echo __('%1% sent you %2% %community% sub admin request message.', array('%1%' => $fromMember->getName(), '%2%' => $community->name)) ?>

<?php if ($message): ?>
<?php echo __('Message') ?>:
<?php echo $message ?>
<?php endif; ?>


<?php echo __('Please allow or reject this request in the confirmation list page.') ?>

<?php echo app_url_for('pc_frontend', '@confirmation_list?category=community_sub_admin_request', true) ?>
