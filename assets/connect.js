/* 
 * Requires ethers-5.1.esm.min.js
 * @see https://docs.ethers.io/v5/getting-started/
 */

// A Web3Provider wraps a standard Web3 provider, which is
// what Metamask injects as window.ethereum into each page
// const provider = new ethers.providers.Web3Provider(window.ethereum, "any");
let _provider;
let _signer;
let _address;

const getProvider = async () => {
    if (!_provider) {
        _provider = new ethers.providers.Web3Provider(window.ethereum, "any")
    }
    return _provider
}

const getSigner = async () => {
    const provider = await getProvider();
    if (!_signer) {
        _signer = await provider.getSigner()
    }
    return _signer
}

const getSelectedAddress = async () => {
    const signer = await getSigner()
    if (!_address) {
        _address = await signer.getAddress().catch(() => { });
    }
    return _address;
}

const getWalletConnection = async () => {
    try {
        const { ethereum } = window
        const provider = await getProvider();

        if (!ethereum) {
            console.log("Wallet not installed.");
            return
        }

        // Prompt wallet to connect.
        await provider.send("eth_requestAccounts", [])

        return await getSelectedAddress()
    } catch (error) {
        console.error(error);
    }
}

const loginBySigning = async (walletAddress, signingMessage, nonce, loginUrl) => {
    const signer = await getSigner();
    const signedMessage = await signer.signMessage(JSON.stringify(signingMessage))

    let response = await fetch(loginUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json;charset=utf-8',
            'X-WP-Nonce': nonce,
            'X-Signed-Message': signedMessage,
        }
    });

    if (response.ok) { // if HTTP-status is 200-299
        const json = await response.json()
        if (json.user) { location.reload(); }
    }

    return walletAddress;
}

const attach_events = async (params) => {

    // Changing wallet.
    window.ethereum.on('accountsChanged', async (accounts) => {
        const {logoutUrl,nonce} = params;
        let response = await fetch(logoutUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
                'X-WP-Nonce': nonce,
            }
        });
    
        if (response.ok) { // if HTTP-status is 200-299
            const json = await response.json()
            location.reload()
        }
    })

    // Get login trigger.
    const cssTrigger = params.walletCssTrigger
    if ( cssTrigger ) {
        document.querySelectorAll(`.${cssTrigger}`).forEach(function(el){ 
            el.addEventListener('click', async () => {
                if (params.nonce && params.user.ID === 0) {
                    // Login by signing the nonce.
                    await loginBySigning(params.connectedWallet, params.signingMessage, params.nonce, params.loginUrl);
                }
            })
        })
    }
}

const init = async () => {
    try {
        const connectedWallet = await getWalletConnection()
        if (!connectedWallet) {
            console.log('Wallet not connected.')
            return
        }

        const { nonce, user, signingMessage, baseUrl, autoConnectWallet } = web3wp_connect        
        const loginUrl = `${baseUrl}login`;
        const logoutUrl = `${baseUrl}logout`;

        await attach_events({ 
            logoutUrl,
            loginUrl,
            ...web3wp_connect,
            connectedWallet, 
            signingMessage,
        });

        // If the user is not logged in, attempt a login.
        if (nonce && user.ID === 0 && autoConnectWallet) {
            // Login by signing the nonce.
            await loginBySigning(connectedWallet, signingMessage, nonce, loginUrl);
        }

    } catch (error) {
        console.error(error);
    }
}

init();

