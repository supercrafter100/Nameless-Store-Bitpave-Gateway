<form action="" method="post">
    <div class="card shadow border-left-primary">
        <div class="card-body">
            <h5><i class="icon fa fa-info-circle"></i> Info</h5>
            The values of these fields are hidden for security reasons.<br />If you are updating these settings, please enter both the client ID, client secret and the wallet together.
        </div>
    </div>

    <br />

    <div class="form-group">
        <label for="inputClientId">Client ID</label>
        <input class="form-control" type="text" id="inputClientId" name="client_id" placeholder="The values of these fields are hidden for security reasons.">
    </div>
    <div class="form-group">
        <label for="inputClientSecret">Client Secret</label>
        <input class="form-control" type="text" id="inputClientSecret" name="client_secret" placeholder="The values of these fields are hidden for security reasons.">
    </div>
    <div class="form-group">
        <label for="inputWallet">Wallet</label>
        <input class="form-control" type="text" id="inputWallet" name="wallet" placeholder="The values of these fields are hidden for security reasons.">
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="inputEnabled" name="enable" type="checkbox" class="custom-control-input"{if $ENABLE_VALUE eq 1} checked{/if} />
        <label class="custom-control-label" for="inputEnabled">Enable Payment Method</label>
    </div>

    <div class="form-group">
        <input type="hidden" name="token" value="{$TOKEN}">
        <input type="submit" value="{$SUBMIT}" class="btn btn-primary">
    </div>
</form>