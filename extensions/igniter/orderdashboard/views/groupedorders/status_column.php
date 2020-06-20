<div class="control-statuseditor"
        data-control="status-editor-popup-<?= $record->order_id; ?>"
        data-record-id="<?= $record->order_id; ?>"
        data-alias="formStatusId"
        >
    <span class="label label-default" style="background-color: <?= $record->status_color; ?>;">
        <a href="#" class="text-white" data-editor-control="load-status" data-record-id="<?= $record->order_id; ?>" role="button"><?= $value; ?></a>
    </span>
</div>