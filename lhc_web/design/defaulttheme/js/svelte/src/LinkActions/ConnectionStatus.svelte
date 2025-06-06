<svelte:options customElement={{tag: 'lhc-connection-status', shadow: 'none'}}/>
<script>
    import { lhcList } from '../stores.js';
    import { onMount } from 'svelte';
    import { t } from "../i18n/i18n.js";

    export let type = "pending_chats";

    let isOnline = navigator.onLine;
    let nodeJSConnected =  lh?.nodejsHelperOptions?.socketConnected === true ? true : (lh.nodejsHelperOptions ? false : null);
    let nodeJSConnectedInitial = false;
    let requestInProgress = false;

    function startListeners() {
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) refreshOnReturn();
        });

        window.addEventListener('focus', refreshOnReturn);
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
    }

    async function refreshOnReturn() {

        try {
            if (!navigator.onLine || requestInProgress === true) {
                return;
            }

            requestInProgress = true;

            const responseTrack = await fetch(WWW_DIR_JAVASCRIPT  + 'chat/verifytoken', {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    'X-CSRFToken' : confLH.csrf_token
                }
            });

            if (!responseTrack.ok) {
                throw new Error("Network response was not OK [" + responseTrack.status + "] ["+ responseTrack.statusText+"]");
            }

            const data = await responseTrack.json();

            if (!data.verified) {
                document.location.reload();
            }

            ee.emitEvent('angularLoadChatList');

            requestInProgress = false;

        } catch(err) {
            document.location.reload();
        }
    }

    function updateOnlineStatus() {
        isOnline = navigator.onLine;
        if (isOnline) {
            refreshOnReturn();
        }
    }

    ee.addListener('socketConnected',() => {
        nodeJSConnected = true;

        // Skip initial connection signaling
        if (nodeJSConnectedInitial === false) {
            nodeJSConnectedInitial = true;
            return;
        }

        refreshOnReturn();

    });

    ee.addListener('socketDisconnected',() => {
        nodeJSConnected = false;
        // On IOS Safari this gets triggered instantly otherwise and page refreshes just
        setTimeout( () => {
            refreshOnReturn();
        }, 60000);
    });

    onMount(() => {
        startListeners();
    });

</script>


<div class="py-2 text-center fs12">
{#if $lhcList.lhcUpdatedAtActivity >0}<span title={$t("homepage.lau")}><span class="material-icons">progress_activity</span>{Math.floor(Date.now() / 1000) - $lhcList.lhcUpdatedAtActivity} s.</span>{/if}

{#if !isOnline}
        <span class="text-danger"><span class="material-icons">wifi_off</span>{$t("homepage.no_connection")}</span>
    {:else}
        {#if nodeJSConnected !== null}<span class={"material-icons" + (nodeJSConnected ? '' : " text-warning")} title={$t("homepage.node_js_status")}>{nodeJSConnected ? 'bigtop_updates' : 'signal_disconnected'}</span>{/if}
        {#if $lhcList.lhcBLockSync}
            <span class="text-danger" ><span class="material-icons">sync_problem</span>{$t("homepage.sync_blocked")}</span>
                {:else }
            <span class={($lhcList.lhcConnectivityProblem ? " text-danger" : " text-muted")} title={$t("homepage.last_updated_at")}><span class={"material-icons"}>{$lhcList.lhcListRequestInProgress ? 'sync' : ($lhcList.lhcConnectivityProblem ? 'sync_problem' : 'history')}</span>{$lhcList.lhcUpdatedAt}</span>
        {/if}
{/if}

    {#if !isOnline || $lhcList.lhcConnectivityProblem}
        {#if $lhcList.lhcMessageConnection}
            {@html $lhcList.lhcMessageConnection}
        {:else}
            <div class="text-danger fs14"><span class="material-icons">wifi_off</span>{$t("homepage.connection_explain")}</div>
        {/if}
    {/if}

</div>
