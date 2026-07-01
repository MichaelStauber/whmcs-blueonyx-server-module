<h3>Generate SSL Certificate</h3>

{if $errorMessage}
    <div class="alert alert-danger">{$errorMessage}</div>
{else}
    <p>Redirecting to the SSL management page ...</p>
    <p>If you are not redirected, <a href="https://{$serverhostname}:81/ssl/letsencryptCert?group={$vsiteInfo.name}" target="_blank">click here</a>.</p>
{/if}

<a href="clientarea.php?action=productdetails&id={$serviceid}" class="btn btn-default">Back</a>