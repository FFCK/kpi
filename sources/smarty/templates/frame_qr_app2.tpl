{if isset($dataUrl) && $dataUrl}
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
        <img src="{$dataUrl}" alt="QR Code" style="max-width: 500px; width: 80%; height: auto; display: block; margin: 0 auto;">
        <a href="{$targetUrl}" style="display: inline-block; margin-top: 10px; padding: 4px 10px; background: white; color: #333; word-break: break-all; border-radius: 4px;">{$targetUrl}</a>
    </div>
{/if}
