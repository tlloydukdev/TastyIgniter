<?php
echo "cart";
if ($locationCurrent->hasDelivery() OR $locationCurrent->hasCollection()) { ?>
    <?php
    $deliveryTime = Carbon\Carbon::parse($location->deliverySchedule()->getOpenTime());
    $collectionTime = Carbon\Carbon::parse($location->collectionSchedule()->getOpenTime());
    ?>
    <div class="btn-group btn-group-toggle w-100 text-center order-type" data-toggle="buttons"
    data-control="order-type-toggle"
        data-handler="<?= $orderTypeEventHandler; ?>">
        <?php if ($locationCurrent->hasDelivery()) { ?>
            <label
                class="btn btn-<?= $location->orderTypeIsDelivery() ? 'primary' : 'light'; ?> <?= $location->orderTypeIsDelivery() ? 'active' : ''; ?>" style="<?= $location->orderTypeIsDelivery() ? 'background-color: #FC6C35!important;' : ''; ?>">
                <input
                    type="radio"
                    name="order_type"
                    data-cart-toggle="order-type"
                    value="delivery" <?= $location->orderTypeIsDelivery() ? 'checked="checked"' : ''; ?>
                >&nbsp;&nbsp;
                <strong><?= lang('igniter.local::default.text_delivery'); ?></strong>
                <span
                    class="small center-block">
                        <?php if ($location->deliverySchedule()->isOpen()) { ?>
                            <?= sprintf(lang('igniter.local::default.text_in_minutes'), $locationCurrent->deliveryMinutes()); ?>
                        <?php }
                        else if ($location->deliverySchedule()->isOpening()) { ?>
                            <?= sprintf(lang('igniter.local::default.text_starts'), $deliveryTime->isoFormat($cartBoxTimeFormat)); ?>
                        <?php }
                        else { ?>
                            <?= lang('igniter.local::default.text_is_closed'); ?>
                        <?php } ?>
                    </span>
            </label>
        <?php } ?>
        <?php if ($locationCurrent->hasCollection()) { ?>
            <label
                class="btn btn-<?= ($location->orderType() == 'collection') ? 'primary' : 'light'; ?> <?= ($location->orderType() == 'collection') ? 'active' : ''; ?>" style="<?= $location->orderType() == 'collection' ? 'background-color: #FC6C35!important;' : ''; ?>">
                <input
                    type="radio"
                    name="order_type"
                    data-cart-toggle="order-type"
                    value="collection" <?= ($location->orderType() == 'collection') ? 'checked="checked"' : ''; ?>
                >&nbsp;&nbsp;
                <strong><?= lang('igniter.local::default.text_collection'); ?></strong>
                <span
                    class="small center-block">
                        <?php if ($location->collectionSchedule()->isOpen()) { ?>
                            <?= sprintf(lang('igniter.local::default.text_in_minutes'), $locationCurrent->collectionMinutes()); ?>
                        <?php }
                        else if ($location->collectionSchedule()->isOpening()) { ?>
                            <?= sprintf(lang('igniter.local::default.text_starts'), $collectionTime->isoFormat($cartBoxTimeFormat)); ?>
                        <?php }
                        else { ?>
                            <?= lang('igniter.local::default.text_is_closed'); ?>
                        <?php } ?>
                    </span>
            </label>
        <?php } ?>
    </div>    
    <div class="col-sm-12 mb-3 mb-sm-3 pr-0 pl-0">
        <div
            id="local-timeslot"
            data-control="timepicker"
            data-time-slot="<?= e(json_encode($locationTimeslot)); ?>"
        >
            <?= partial('@timeslot'); ?>
        </div>
    </div>
    <?php if ($location->orderTypeIsDelivery()) { ?>
        <p class="text-muted text-center">
            <?= ($minOrderTotal = $location->minimumOrder($cart->subtotal()))
                ? lang('igniter.local::default.text_min_total').': '.currency_format($minOrderTotal)
                : lang('igniter.local::default.text_no_min_total');
            ?>
        </p>
    <?php } ?>
<?php } ?>
