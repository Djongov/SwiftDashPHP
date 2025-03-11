# Authentication

The app supports local authentication and from the following providers:

- Microsoft Work or schoold (Entra ID) accounts
- Microsoft Personal (LIVE) accounts
- Google accounts

All of the authentication mechanisms are implemented through the use of JWT tokens, even the local.

## Session handler

The JWT token is stored either in a httpOnly authentication cookie or in the PHP Session. Default is session. It is configurable from the `/config/site-settings.php` in the `AUTH_HANDLER` constant.

## Local

Local authentication happens through modern oauth JWT tokens authenticaiton. When the user successfully provides a correct username and password, a JWT token is generated and signed by the `/Views/auth/local.php` script and stored in the handler mechanism. it is signed using the `JWT_PRIVATE_KEY` from .env file.

### Setup

You can setup local authentication by generating a public private key pair. 

Generate a Private Key

``` bash
openssl genpkey -algorithm RSA -out private-key.pem
```

Generate a Public Key from the Private Key

``` bash
openssl rsa -pubout -in private-key.pem -out public-key.pem
```

Next you need to base64 encode them and then place them in the .env file as the following:

- JWT_PUBLIC_KEY - This needs to be a base64 encoded public key
- JWT_PRIVATE_KEY - This needs to be a base64 encoded private key
- LOCAL_LOGIN_ENABLED=true or false

> Note that you can generate the key pair automatically, if you generate your .env file via the `/create-env` endpoint which only works if you don't have an existing .env file in the root

## Microsoft Entra Id

This works by either creating an app registration in your Azure tenant (works with both single-tenant, multi-tenant both) and providing the client id, tenant id and a secret in the .env file.

### App Registration setup

1. Login to your Azure portal -> Microsoft Entra Id -> App registrations -> + New App Registration
2. If you want a multitenant app, you have to choose from Authentication -> Supported account types -> Accounts in any organizational directory (Any Microsoft Entra ID tenant - Multitenant)
3. In Authentication -> Put the redirect uri
    - https://example.com/auth/azure-ad
    - https://example.com/auth/azure/azure-ad-code-exchange
4. In Authentication -> Enable ID Tokens, Access tokens
5. In Certificates & secrets -> Create a new secret and record it
6. In Token Configuration (optional) -> Add optional claims - ctry, ipaddr
7. In App Roles -> Create app role -> display name => Admins, allowed member types => Users/Groups, Value = administrator, description => This role provides administrator access to the app
8. To assign admins browse the app registration as Enterprise app -> Users and groups -> Add user/group -> Select users -> Role should be only the new admin role

Now, in the .env file place the following:

- ENTRA_ID_LOGIN_ENABLED=true or false
- ENTRA_ID_TENANT_ID="XXXX"
- ENTRA_ID_CLIENT_ID="XXXX"
- ENTRA_ID_CLIENT_SECRET="XXXX"

> Note that you can do this in the `/create-env` endpoint as well, if you are generating the .env file from there

If your app registration is multitenant, you have to go to `/config/azure-ad-auth-config.php` and change constant `ENTRA_ID_MULTITENANT` to true

## Microsoft Live (personal)

If you want personal (live) accounts to login to your app you need to either create a separate app registration or use the same app registration you use for Entra Id logins.

### Use the same as Entra Id

If you want to enable your existing App registration, you need to make it multitenant as well. So the way to do it is through the Manifest editor. Go to your existing App registration from the previous step, go to Manifest, find `"signInAudience"` and give it a value of ```AzureADandPersonalMicrosoftAccount```. Add the `https://example.com/auth/azure/mslive-code-exchange` redirect URI.

### Dedicated MS Live app registration

If you want to create a new and dedicated app registration for your MS LIve logins, follow these steps:

1. Login to your Azure portal -> Microsoft Entra Id -> App registrations -> + New App Registration
2. In Authentication -> Put the redirect uri
    - https://example.com/auth/azure-ad
    - https://example.com/auth/azure/mslive-code-exchange

> App registrations with only Personal Microsoft Accounts audience cannot have App roles

## Google

Follow these steps if you want a Google authentication.

1. Login to https://console.cloud.google.com/
2. Select the project you want to work in
3. Go to API and Services
4. If you don't have a OAuth consent screen, create one
5. Go to Cedentials
6. Create a Oauth client ID credential
7. Authorised JavaScript origins - https://example.com
8. Authorised redirect URIs - `https://example.com/auth/google`
9. Save
10. Enable it

Record the client ID and secret into the .env file as:

- GOOGLE_LOGIN_ENABLED=true or false
- GOOGLE_CLIENT_ID="XXXXXX.apps.googleusercontent.com"
- GOOGLE_CLIENT_SECRET="XXXXX"
