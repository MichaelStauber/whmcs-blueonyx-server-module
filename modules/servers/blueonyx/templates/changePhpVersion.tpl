<h3>Change PHP Version</h3>

{if $successMessage}
    <div class="alert alert-success">{$successMessage}</div>
{/if}
{if $errorMessage}
    <div class="alert alert-danger">{$errorMessage}</div>
{/if}

<form method="post" action="clientarea.php?action=productdetails&id={$serviceid}&customAction=changePhpVersion">
    <div class="form-group">
        <label for="phpVersion">Current PHP Version: {$phpInfo.current_version}</label>
        <select name="phpVersion" id="phpVersion" class="form-control">
            {foreach from=$phpInfo.available_versions item=version}
                <option value="{$version}" {if $phpInfo.current_version eq $version}selected{/if}>
                    {if $version eq 'PHPOS'}System Default (PHPOS){elseif $version eq 'PHP83'}PHP 8.3{elseif $version eq 'PHP84'}PHP 8.4{else}{$version}{/if}
                </option>
            {/foreach}
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Change PHP Version</button>
    <a href="clientarea.php?action=productdetails&id={$serviceid}" class="btn btn-default">Back</a>
</form>