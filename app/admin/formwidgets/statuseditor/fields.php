<div class="form-fields p-0">
    <input type="hidden" name="context" value="<?= $this->isStatusMode ? 'status' : 'assignee' ?>">
    <!-- required for push / grubs up -->
    <input type="hidden" name="statusData[order_id]" value="<?= $this->model->order_id; ?>">
    <input type="hidden" name="statusData[customer_id]" value="<?= $this->model->customer_id; ?>">
    <?php foreach ($formWidget->getFields() as $field) { ?>
        <?= $formWidget->renderField($field) ?>
    <?php } ?>
</div>
