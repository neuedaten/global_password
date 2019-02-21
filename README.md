# TYPO3 global password

Password protection for a complete TYPO3 frontend. Useful for development and staging servers.

## .env

### Password:

`TYPO3__GLOBAL_PASSWORD="Password123!"`

### Config file name (optional):
`TYPO3__GLOBAL_PASSWORD_CONFIG_FILE="global-password.yaml"`

save config file to `config` directory

## Config file

e.g. `config/global-password.yaml`

```
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