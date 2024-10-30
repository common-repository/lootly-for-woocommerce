<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 06.03.2019
 * Time: 15:32
 *
 * @global  data
 */
?>

<div class="lootly__wrap">
    <header class="lootly__header">
        <div class="lootly__logo__wrap">
            <div class="lootly__logo"></div>
        </div>
        <div class="lootly__header__bottom">
            <p>Don`t have a Lootly account? <a href="https://lootly.io/pricing" target="_blank">Click here</a>&nbsp;to create a new account.</p>
        </div>
    </header>
    <section>
        <h1>API Settings</h1>
        <div class="section-content">
            <p>
                Enter in your account Information and than click "Verify Settings" to connect your WooCommerce Site to your Lootly account. Be sure to click save at the bottom after successfully verifying.
            </p>
            <form id="api__form" method="post">
                <p><label for="lootly__email">Account Email</label><input id="lootly__email" value="<?php if($data['email']) echo $data['email']; ?>" name="email" type="email"></p>
                <p><label for="lootly__api_key">API Key</label><input id="lootly__api_key" value="<?php if($data['email']) echo $data['api_key']; ?>" name="api_key" type="text"></p>
                <p><label for="lootly__api_secret">API Secret</label><input id="lootly__api_secret" value="<?php if($data['email']) echo $data['api_secret']; ?>" name="api_secret" type="text"></p>
                <p><input class="button button-primary" type="submit" value="Verify Settings"><?php
                    if(isset($data['verification'])){
                        if($data['verification']){
                            echo "<span class='verification success'>Lootly successfully connected</span>";
                            if(isset($data['installed']) && $data['installed']){
                                echo "<span class='verification success'>App installed!</span>";
                            }else echo "<span class='verification fail'>App not installed!</span>";
                        }else echo "<span class='verification fail'>Verification Failed, please check your credentials</span>";

                    }
                    ?></p>
                <input type="hidden" name="lootly_update_settings" value="<?php if($data['lootly_update_settings']) echo $data['lootly_update_settings']?>"/>
            </form>

        </div>
    </section>
    <section>
        <h1>Order Settings</h1>
        <div class="section-content">
            <p>
                Select the order status below when you would like WooCommerce orders to be sent to Lootly.
            </p>
            <form id="options__form" method="post">
                <p><label for="lootly__status">Order Status</label>
                    <?php
                    $current_status = $data['status'] ? $data['status']: 'completed';
                    ?>
                    <select id="lootly__status" name="status" type="email">
                        <option value='completed' <?php echo $current_status=='completed'? 'selected' : ''; ?>>&nbsp;&nbsp;Completed</option>
                        <option value='processing' <?php echo $current_status=='processing'? 'selected' : ''; ?>>&nbsp;&nbsp;Processing</option>
                    </select>
                </p>
                <p>
                    <label for="lootly__autoapply">Auto apply coupon code</label>
                    <?php
                    $autoapply = isset($data['autoapply']) ? $data['autoapply']: '0';
                    ?>
                    <select id="lootly__autoapply" name="autoapply">
                        <option value='1' <?php echo $autoapply=='1'? 'selected' : ''; ?>>&nbsp;&nbsp;Yes</option>
                        <option value='0' <?php echo $autoapply=='0'? 'selected' : ''; ?>>&nbsp;&nbsp;No</option>
                    </select>
                </p>
                <p><input class="button button-primary" type="submit" value="Save Settings"></p>
                <input type="hidden" name="lootly_update_settings" value="<?php echo $data['lootly_update_settings']?>"/>
            </form>
        </div>
    </section>
</div>