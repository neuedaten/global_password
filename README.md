# TYPO3 global password

Password protection for a complete TYPO3 frontend. Useful for development and staging servers.

Set the password in your env file then configure if it is active or not via the site config

1. Add the password to the `.env` file
2. Add the configuration to `config/sites/*/config.yaml`
3. Customise the login screen if desired

The password needs to be both present in your `.env` and activated in the `config.yaml` to show

## .env

Set the password with :

`TYPO3__GLOBAL_PASSWORD="Password123!"`

## Site Configuration YAML

To activate the password for that site on all environments **where the password is in the `.env` file** then add:

```yaml
globalPassword:
  enabled: true
```

If you wish to disable/enable the password for a specific environment, you can do this with:

```yaml
globalPassword:
  enabled: false
  variants:
    -
      enabled: true
      condition: 'applicationContext == "Production/Staging"'
```

Where `condition` is the same as the `baseVariants` conditions of the domain names.

Don't forget to enable this **for each** `config.yaml` in your install. 

## Config file

If you wish to configure the password login screen, you can do so by creating a `config/global-password.yaml` file and specifying the following in your `.env` file

`TYPO3__GLOBAL_PASSWORD_CONFIG_FILE="global-password.yaml"`

e.g. `config/global-password.yaml`

You can then override the following:

```yaml
texts:
  title: "Page title"
  htmlAbove: "some text <b>above</b> the form"
  htmlBelow: "some text <b>below</b> the form"
  passwordPlaceholder: "Password"
  rememberMe: "remember me"
  login: Login
  wrongPassword: "Please check your password"
```

## Logout

add this get parameter to your url:
``?global-password-logout=1``

Copyright (c) 2019 Bastian Schwabe <bas@neuedaten.de>

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
