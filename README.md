# Silently Logged In Checkout

**Contributor:** Téo F  
**Tags:** woocommerce, checkout, authentication, otp, email, silent login, guest checkout  
**Requires WordPress:** 5.0+  
**Requires WooCommerce:** 3.0+  
**PHP Version:** 7.4+  
**License:** GPL-2.0+ ([http://www.gnu.org/licenses/gpl-2.0.txt](http://www.gnu.org/licenses/gpl-2.0.txt))

Enable seamless automatic user login during WooCommerce checkout via email verification and OTP code. Perfect for stores that need a guest-like experience without requiring an existing customer account.

---

## Description

The "Silently Logged In Checkout" plugin streamlines the login process for your WooCommerce customers. Designed for stores that want a guest-like checkout experience while requiring an existing customer account at the time of purchase, this plugin allows users to:

1. Simply enter their email address
2. Receive a 6-digit OTP (One-Time Password) code via email
3. Verify the code received
4. Be automatically logged in and have a WooCommerce account created
5. Access the checkout page directly

### Benefits

- ✅ Seamless user experience with zero friction
- ✅ Customers don't need to choose a password (reduces forgotten passwords)
- ✅ Compatible with modern WordPress themes (including FSE - Full Site Editing)
- ✅ Simple configuration through WooCommerce admin menu
- ✅ Personalized emails including your store name
- ✅ Secure OTP code management with WordPress Transients

---

## Installation

### From WordPress Dashboard

1. Download the plugin ZIP file
2. Go to **Plugins > Add New** in your WordPress dashboard
3. Click **Upload Plugin** and select the ZIP file
4. Activate the plugin
5. Go to **WooCommerce > Silently Logged In** to configure the pages

### Manual Installation

1. Extract the ZIP file
2. Upload the `silently-loggedin-checkout` folder to `/wp-content/plugins/`
3. Activate the plugin from the **Plugins** menu
4. Configure the plugin (see [Configuration](#configuration) section)

---

## Configuration

### Initial Settings

After activation, go to **WooCommerce > Silently Logged In Checkout** to configure:

#### 1. Email Form Page
- Select the page that will contain the `[slc_email_prompt]` form
- This page will be showcased after a user session is cleared
- Users will enter their email address here to start the process

#### 2. OTP Verification Page
- Select the page that will contain the `[slc_otp_verify]` form
- This page will display a form to enter the OTP code received by email
- Must be the same for all customers to ensure consistency

#### 3. Redirect Page (Logged-in Users)
- Select the page where already logged-in users should be redirected if they visit either of the above pages
- Usually this is your WooCommerce checkout page or homepage
- If not configured, automatically redirects to the default WooCommerce checkout page

### Creating the Pages

You must create at least two WordPress pages containing the shortcodes:

#### Page 1: Email Form

- **Create:** Pages > Add New
- **Suggested Title:** "Quick Login" or "Email Verification"
- **Content:** Simply add `[slc_email_prompt]` in the editor
- **Save:** Save and note the page ID
- **Configure:** Select it in the plugin configuration

#### Page 2: OTP Form

- **Create:** Pages > Add New
- **Suggested Title:** "Code Verification" or "Confirm Your Code"
- **Content:** Simply add `[slc_otp_verify]` in the editor
- **Save:** Save and note the page ID
- **Configure:** Select it in the plugin configuration

---

## Usage

### Normal User Flow

```
1. Non-logged-in user visits checkout page
                    ↓
2. Redirect to email form page
                    ↓
3. User enters their email
                    ↓
4. Plugin generates OTP and sends email
                    ↓
5. User redirected to verification page
                    ↓
6. User enters OTP code received
                    ↓
7. Plugin validates, creates account and logs in user
                    ↓
8. Redirect to checkout page
                    ↓
9. User completes purchase while logged in
```

**Detailed Steps:**

1. **Non-logged-in user** visits the WooCommerce checkout page
2. **WooCommerce** redirects them to the email form page (configurable via WooCommerce)
3. **User** enters their email and clicks "Send Code"
4. **Plugin**:
   - Generates a 6-digit OTP code
   - Stores the code securely for 10 minutes
   - Sends an email with the code
5. **User** receives the email and is redirected to the verification page
6. **User** enters their OTP code
7. **Plugin**:
   - Validates the code
   - Creates a WooCommerce account with the provided email
   - Automatically logs in the user
8. **User** is redirected to checkout
9. **User** completes the purchase as a logged-in user

### Style Customization

The plugin uses a CSS file to style the forms: `public/css/silently-loggedin-checkout-public.css`

**Available CSS Classes:**

```css
.slc-form-wrapper       /* Main form container */
.slc-error              /* Error messages (red color) */
input[type="email"]     /* Email fields */
input[type="text"]      /* Text fields */
button[type="submit"]   /* Submit buttons */
```

Modify the CSS file directly or add custom CSS to your theme to match your store's visual identity.

### Emails

The plugin automatically sends emails when generating the OTP code.

**Email Sent:**
- **Subject:** "Your login code [Store Name]"
- **Content:**
  - Personalized greeting
  - 6-digit OTP code in bold
  - Validity duration (10 minutes)
  - Security note

The email is sent in HTML format and automatically includes your store name.

---

## Technical Documentation

### Shortcodes

#### `[slc_email_prompt]`

Displays the email input form.

```
[slc_email_prompt]
```

- **Parameters:** None
- **Use:** In a WordPress page
- **Displays:** Form with email field and submit button

#### `[slc_otp_verify]`

Displays the OTP code input form.

```
[slc_otp_verify]
```

- **Parameters:** None
- **Use:** In a WordPress page
- **Displays:** Form with OTP field and verification button
- **Redirect:** Redirects to email form if user accesses without going through normal flow

### Plugin Structure

```
silently-loggedin-checkout/
├── silently-loggedin-checkout.php          # Main file
├── uninstall.php                            # Cleanup on deletion
├── admin/
│   ├── class-silently-loggedin-checkout-admin.php
│   ├── js/silently-loggedin-checkout-admin.js
│   ├── css/silently-loggedin-checkout-admin.css
│   └── partials/silently-loggedin-checkout-admin-display.php
├── includes/
│   ├── class-silently-loggedin-checkout.php
│   ├── class-silently-loggedin-checkout-loader.php
│   ├── class-silently-loggedin-checkout-i18n.php
│   ├── class-silently-loggedin-checkout-activator.php
│   └── class-silently-loggedin-checkout-deactivator.php
├── public/
│   ├── class-silently-loggedin-checkout-public.php
│   ├── js/silently-loggedin-checkout-public.js
│   ├── css/silently-loggedin-checkout-public.css
│   └── partials/silently-loggedin-checkout-public-display.php
├── languages/
│   └── silently-loggedin-checkout.pot
└── README.md
```

---

## Frequently Asked Questions

### Q: What is the OTP code expiration time?

**A:** 10 minutes. After this time, the user must start over by submitting their email again.

### Q: How many times can a user enter the wrong code?

**A:** Maximum 3 attempts. After 3 failed attempts, the OTP code is invalidated and the user must start over.

### Q: Does the plugin work with all WordPress themes?

**A:** Yes, the plugin works with all themes, including Full Site Editing (FSE) themes. The plugin uses WordPress pages independent of the theme, which means your custom header and footer will display normally.

### Q: How does the plugin handle passwords?

**A:** The plugin generates WooCommerce accounts with random passwords. Users can change their password later from their account dashboard if they wish.

### Q: How do I test the plugin?

**A:** Follow these steps:
1. Log out
2. Go to checkout page
3. Enter a valid email address
4. Check your email for the OTP code
5. Enter the code on the verification page
6. Confirm you are automatically logged in

### Q: What happens if an already logged-in user visits the page?

**A:** They are automatically redirected to the page configured in the settings (by default, the WooCommerce checkout page).

### Q: Why am I not receiving emails?

**A:** Make sure your WordPress email server is properly configured. See the [Support](#support) section for more details.

---

## Security

- 🔒 **OTP Codes:** Generated cryptographically and stored via WordPress Transients API
- 🔒 **CSRF Validation:** All forms include WordPress nonce verification
- 🔒 **Email Hashing:** Emails are hashed (MD5) when storing the OTP code
- 🔒 **Attempt Limitation:** Maximum 3 incorrect code attempts before invalidation
- 🔒 **HTTPS Recommended:** While not required, HTTPS is strongly recommended

---

## Support

### Troubleshooting

To resolve common issues:

- ✓ Check that **WooCommerce is activated** and up to date
- ✓ Verify that **pages are correctly configured** in admin menu
- ✓ Ensure your **WordPress email server** is properly configured
- ✓ Verify that both pages contain the `[slc_email_prompt]` and `[slc_otp_verify]` shortcodes
- ✓ In plugin configuration, ensure all three pages are selected

### Contact Support

For questions or issues:
- Consult this documentation and the "Frequently Asked Questions" section
- Check WordPress error logs in `/wp-content/debug.log`
- Verify with your hosting provider that the `wp_mail()` function works correctly

---

## Changelog

### Version 1.0.0

- ✨ Initial release
- ✨ Email and OTP login forms
- ✨ Automatic WooCommerce account creation
- ✨ Smart redirect for logged-in users
- ✨ Configuration via WooCommerce admin panel
- ✨ FSE (Full Site Editing) theme support
- ✨ Personalized HTML emails with store name

---

## License

This plugin is licensed under **GPL-2.0+**. See [LICENSE.txt](LICENSE.txt) for details.

---

## Author

Developed by **Téo F**

---

## Resources

- [WordPress Plugin Documentation](https://developer.wordpress.org/plugins/)
- [WooCommerce Documentation](https://woocommerce.com/documentation/)
- [WordPress Transients API](https://developer.wordpress.org/plugins/caching/working-with-transients/)
