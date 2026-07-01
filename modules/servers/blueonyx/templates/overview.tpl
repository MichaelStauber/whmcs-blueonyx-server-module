<!-- Custom Actions Section -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Quick Actions</h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-6">
                <a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=changePhpVersion" class="btn btn-primary btn-block mb-2">Change PHP Version</a>
            </div>
            <div class="col-sm-6">
                <a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=generateSsl" target="_blank" class="btn btn-primary btn-block mb-2">Generate SSL Certificate</a>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=fileManager" target="_blank" class="btn btn-primary btn-block mb-2">File Manager</a>
            </div>
            <div class="col-sm-6">
                <a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=phpMyAdmin" target="_blank" class="btn btn-primary btn-block mb-2">phpMyAdmin</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <a href="{$loginLink}" target="_blank" class="btn btn-success btn-block">
                Login to BlueOnyx Control Panel
            </a>
        </div>
    </div>

</div>

<h3>{$LANG.clientareaproductdetails}</h3>

<hr>

<div class="row">
    <div class="col-sm-5">
        {$LANG.clientareahostingregdate}
    </div>
    <div class="col-sm-7">
        {$regdate}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        {$LANG.orderproduct}
    </div>
    <div class="col-sm-7">
        {$groupname} - {$product}
    </div>
</div>

{if $type eq "server"}
    {if $domain}
        <div class="row">
            <div class="col-sm-5">
                {$LANG.serverhostname}
            </div>
            <div class="col-sm-7">
                {$domain}
            </div>
        </div>
    {/if}
    {if $dedicatedip}
        <div class="row">
            <div class="col-sm-5">
                Vsite IP Address
            </div>
            <div class="col-sm-7">
                {$dedicatedip}
            </div>
        </div>
    {/if}
    {if $assignedips}
        <div class="row">
            <div class="col-sm-5">
                {$LANG.assignedIPs}
            </div>
            <div class="col-sm-7">
                {$assignedips|nl2br}
            </div>
        </div>
    {/if}
    {if $ns1 || $ns2}
        <div class="row">
            <div class="col-sm-5">
                {$LANG.domainnameservers}
            </div>
            <div class="col-sm-7">
                {$ns1}<br />{$ns2}
            </div>
        </div>
    {/if}
{else}
    {if $domain}
        <div class="row">
            <div class="col-sm-5">
                {$LANG.orderdomain}
            </div>
            <div class="col-sm-7">
                {$domain}
                <a href="http://{$domain}" target="_blank" class="btn btn-default btn-xs">{$LANG.visitwebsite}</a>
            </div>
        </div>
    {/if}
    {if $username}
        <div class="row">
            <div class="col-sm-5">
                {$LANG.serverusername}
            </div>
            <div class="col-sm-7">
                {$username}
            </div>
        </div>
    {/if}
    {if $serverdata}
        <div class="row">
            <div class="col-sm-5">
                {$LANG.servername}
            </div>
            <div class="col-sm-7">
                {$serverdata.hostname}
            </div>
        </div>
        <div class="row">
            <div class="col-sm-5">
                Server IP Address
            </div>
            <div class="col-sm-7">
                {$serverdata.ipaddress}
            </div>
        </div>
        {if $serverdata.nameserver1 || $serverdata.nameserver2 || $serverdata.nameserver3 || $serverdata.nameserver4 || $serverdata.nameserver5}
            <div class="row">
                <div class="col-sm-5">
                    {$LANG.domainnameservers}
                </div>
                <div class="col-sm-7">
                    {if $serverdata.nameserver1}{$serverdata.nameserver1} ({$serverdata.nameserver1ip})<br />{/if}
                    {if $serverdata.nameserver2}{$serverdata.nameserver2} ({$serverdata.nameserver2ip})<br />{/if}
                    {if $serverdata.nameserver3}{$serverdata.nameserver3} ({$serverdata.nameserver3ip})<br />{/if}
                    {if $serverdata.nameserver4}{$serverdata.nameserver4} ({$serverdata.nameserver4ip})<br />{/if}
                    {if $serverdata.nameserver5}{$serverdata.nameserver5} ({$serverdata.nameserver5ip})<br />{/if}
                </div>
            </div>
        {/if}
    {/if}
{/if}

{if $dedicatedip}
    <div class="row">
        <div class="col-sm-5">
            Vsite IP Address
        </div>
        <div class="col-sm-7">
            {$dedicatedip}
        </div>
    </div>
{/if}

{foreach from=$configurableoptions item=configoption}
    <div class="row">
        <div class="col-sm-5">
            {$configoption.optionname}
        </div>
        <div class="col-sm-7">
            {if $configoption.optiontype eq 3}
                {if $configoption.selectedqty}
                    {$LANG.yes}
                {else}
                    {$LANG.no}
                {/if}
            {elseif $configoption.optiontype eq 4}
                {$configoption.selectedqty} x {$configoption.selectedoption}
            {else}
                {$configoption.selectedoption}
            {/if}
        </div>
    </div>
{/foreach}

{foreach from=$productcustomfields item=customfield}
    <div class="row">
        <div class="col-sm-5">
            {$customfield.name}
        </div>
        <div class="col-sm-7">
            {$customfield.value}
        </div>
    </div>
{/foreach}

{if $lastupdate}
    <div class="row">
        <div class="col-sm-5">
            {$LANG.clientareadiskusage}
        </div>
        <div class="col-sm-7">
            {$diskusage}MB / {$disklimit}MB ({$diskpercent})
        </div>
    </div>
    <div class="row">
        <div class="col-sm-5">
            {$LANG.clientareabwusage}
        </div>
        <div class="col-sm-7">
            {$bwusage}MB / {$bwlimit}MB ({$bwpercent})
        </div>
    </div>
{/if}

<div class="row">
    <div class="col-sm-5">
        {$LANG.orderpaymentmethod}
    </div>
    <div class="col-sm-7">
        {$paymentmethod}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        {$LANG.firstpaymentamount}
    </div>
    <div class="col-sm-7">
        {$firstpaymentamount}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        {$LANG.recurringamount}
    </div>
    <div class="col-sm-7">
        {$recurringamount}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        {$LANG.clientareahostingnextduedate}
    </div>
    <div class="col-sm-7">
        {$nextduedate}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        {$LANG.orderbillingcycle}
    </div>
    <div class="col-sm-7">
        {$billingcycle}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        {$LANG.clientareastatus}
    </div>
    <div class="col-sm-7">
        {$status}
    </div>
</div>

{if $suspendreason}
    <div class="row">
        <div class="col-sm-5">
            {$LANG.suspendreason}
        </div>
        <div class="col-sm-7">
            {$suspendreason}
        </div>
    </div>
{/if}

<hr>

<!-- Vsite Details -->
<div class="vsite-details">
    <h3>Vsite Details</h3>
    <div class="row">
        <div class="col-sm-5">
            Vsite Name
        </div>
        <div class="col-sm-7">
            {$vsiteInfo.name}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-5">
            Vsite IP Address
        </div>
        <div class="col-sm-7">
            {$vsiteInfo.ip_address|escape}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-5">
            Vsite Username
        </div>
        <div class="col-sm-7">
            {$vsiteUsername}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-5">
            Vsite Password
        </div>
        <div class="col-sm-7">
            <span id="vsitePassword">********</span>
            <button type="button" class="btn btn-default btn-xs" onclick="togglePassword()">Show</button>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-5">
            Disk Quota
        </div>
        <div class="col-sm-7">
            {if $vsiteInfo.disk_limit eq 'N/A'}
                {$vsiteInfo.disk_limit}
            {else}
                {$vsiteInfo.disk_limit} {$vsiteInfo.disk_limit_unit}
            {/if}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-5">
            Vsite Over-Quota
        </div>
        <div class="col-sm-7">
            {$vsiteInfo.vsite_over_quota}
        </div>
    </div>
</div>

<hr>

<!-- Vsite SSL Status -->
<div class="ssl-status">
    <h3>Vsite SSL Status</h3>
    <div class="row">
        <div class="col-sm-5">
            SSL Enabled:
        </div>
        <div class="col-sm-7">
            {$sslInfo.enabled}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-5">
            Certificate Expiry:
        </div>
        <div class="col-sm-7">
            {$sslInfo.expiry}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-5">
            Issuer:
        </div>
        <div class="col-sm-7">
            {$sslInfo.issuer}
        </div>
    </div>
</div>

<hr>

<script>
function togglePassword() {
    var passwordField = document.getElementById('vsitePassword');
    if (passwordField.innerHTML === '********') {
        passwordField.innerHTML = '{$vsitePassword|escape:'javascript'}';
        passwordField.nextElementSibling.innerHTML = 'Hide';
    } else {
        passwordField.innerHTML = '********';
        passwordField.nextElementSibling.innerHTML = 'Show';
    }
}
</script>