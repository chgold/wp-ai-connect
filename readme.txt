=== AI Connect - WebMCP Bridge for WordPress ===
Contributors: chgold
Tags: ai, webmcp, oauth, rest-api, artificial-intelligence, automation
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Bridge WordPress & AI Agents with WebMCP Protocol. Secure OAuth 2.0 + JWT authentication for AI-powered content management.

== Description ==

**AI Connect** transforms your WordPress site into an AI-ready platform using the WebMCP protocol. Enable AI agents to interact with your WordPress content through a secure, enterprise-grade OAuth 2.0 + JWT authentication system.

= Features =

* ✅ **WebMCP Protocol Support** - Industry-standard AI integration
* ✅ **OAuth 2.0 + JWT Authentication** - Enterprise-grade security  
* ✅ **Rate Limiting** - Redis-backed or WordPress transients
* ✅ **WordPress Core Tools** - Posts, Pages, Users (5 tools included)
* ✅ **Automatic Manifest Generation** - Zero configuration needed
* ✅ **Developer-Friendly** - Extensible module system

= How It Works =

1. **Create OAuth Client** - Generate credentials for your AI agent
2. **Authorization Flow** - Secure user consent process
3. **Token Exchange** - Get JWT access tokens
4. **API Access** - AI agents can read/write WordPress content
5. **Token Refresh** - Long-lived sessions with refresh tokens

= Included Tools =

**WordPress Core (Free)**

* `wordpress.searchPosts` - Search posts with filters (category, tag, status)
* `wordpress.getPost` - Get single post by ID or slug
* `wordpress.searchPages` - Search WordPress pages
* `wordpress.getPage` - Get single page by ID or slug  
* `wordpress.getCurrentUser` - Get authenticated user info

= Premium Extensions =

**AI Connect Pro** adds:

* 15+ WooCommerce tools (products, cart, orders, customers)
* Forms integration (Contact Form 7, Gravity Forms, WPForms)
* Advanced analytics dashboard
* Priority support

= Use Cases =

* **Content Management Automation** - Let AI agents manage your posts and pages
* **E-commerce Automation** - Automate product searches and order management
* **Customer Support** - AI-powered customer service with order access
* **Development Tools** - Build custom AI integrations with WordPress

= Technical Details =

* RESTful API with OAuth 2.0 (authorization_code flow)
* JWT access tokens (1 hour expiry) + refresh tokens (7 days)
* Redis support for high-performance rate limiting
* WordPress transients fallback for shared hosting
* WebMCP 1.0 compliant manifest

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins → Add New
3. Search for "AI Connect"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Extract and upload to `/wp-content/plugins/ai-connect/`
3. Run `composer install` in the plugin directory
4. Activate through WordPress admin → Plugins menu

= Post-Installation Setup =

1. Navigate to **AI Connect** in admin menu
2. Go to **OAuth Clients** and create a new client
3. Save the `client_id` and `client_secret` (displayed once)
4. Configure rate limits in **Settings** (optional)
5. Use the OAuth endpoints to connect AI agents

**No additional configuration required** - the plugin works out of the box.

== Getting Started with OAuth ==

= Step 1: Create OAuth Client =

1. Go to **AI Connect → OAuth Clients** in WordPress admin
2. Click **Create New OAuth Client**
3. Enter:
   * **Client Name**: e.g., "My AI Agent"
   * **Redirect URI**: Your application's callback URL
4. Click **Create Client**
5. **Save the Client ID and Client Secret** (shown only once!)

= Step 2: Authorization Request =

Direct your users to the authorization URL:

`GET /wp-json/ai-connect/v1/oauth/authorize?client_id=YOUR_CLIENT_ID&redirect_uri=YOUR_REDIRECT_URI&response_type=code&scope=read%20write&state=RANDOM_STATE`

**Required Parameters:**
* `client_id` - Your OAuth client ID
* `redirect_uri` - Must match the one registered
* `response_type` - Always set to `code`
* `scope` - Space-separated: `read`, `write`, or `admin`
* `state` - Random string to prevent CSRF attacks

**Response:** User will be redirected to `YOUR_REDIRECT_URI?code=AUTHORIZATION_CODE&state=RANDOM_STATE`

= Step 3: Exchange Code for Access Token =

Make a POST request to get the access token:

`POST /wp-json/ai-connect/v1/oauth/token`
`Content-Type: application/json`

**Request Body:**
`{
  "grant_type": "authorization_code",
  "code": "AUTHORIZATION_CODE",
  "client_id": "YOUR_CLIENT_ID",
  "client_secret": "YOUR_CLIENT_SECRET"
}`

**Response:**
`{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "def50200...",
  "scope": "read write"
}`

= Step 4: Use the API =

Make authenticated requests to execute tools:

`POST /wp-json/ai-connect/v1/tools/wordpress.searchPosts`
`Authorization: Bearer YOUR_ACCESS_TOKEN`
`Content-Type: application/json`

**Request Body:**
`{
  "search": "hello world",
  "limit": 5
}`

= Step 5: Refresh Token (Optional) =

When the access token expires (after 1 hour), use the refresh token:

`POST /wp-json/ai-connect/v1/oauth/token`
`Content-Type: application/json`

**Request Body:**
`{
  "grant_type": "refresh_token",
  "refresh_token": "YOUR_REFRESH_TOKEN",
  "client_id": "YOUR_CLIENT_ID",
  "client_secret": "YOUR_CLIENT_SECRET"
}`

**Response:** New access token with fresh 1-hour expiry

== Frequently Asked Questions ==

= What is WebMCP? =

WebMCP (Web Model Context Protocol) is a standardized protocol for connecting AI agents to web services. It defines how AI assistants discover, authenticate with, and execute tools on web platforms.

= Is OAuth configuration required? =

Yes, but it's simple! Just create an OAuth client in the plugin settings. The authorization and token URLs are auto-generated - no manual configuration needed.

= Does this work with Claude, ChatGPT, and other AI assistants? =

Yes! AI Connect works with any AI assistant or platform that supports:
* OAuth 2.0 authentication
* REST API calls
* WebMCP protocol (optional but recommended)

Tested with Claude (Anthropic), ChatGPT (OpenAI), Make.com, Zapier, n8n, and custom applications.

= Do I need Redis? =

No, Redis is optional. The plugin works perfectly with WordPress transients. However, Redis is recommended for production sites as it provides more accurate rate limiting and better performance under high load.

= Can I add custom tools? =

Absolutely! AI Connect is built for extensibility. Use the `ai_connect_register_tools` action to add your own custom tools. See the GitHub repository for developer documentation and examples.

= How does the AI agent authentication work? =

**AI Connect uses user-delegated authentication.** The AI agent operates as a specific WordPress user, NOT as a separate superuser.

**What this means:**
* When a user authorizes an AI agent, the agent acts on their behalf
* The agent inherits ALL permissions of that user
* The agent is limited by WordPress user capabilities

**Examples by user role:**

**If Administrator authorizes:**
* ✅ Sees all posts (drafts, private, scheduled)
* ✅ Sees all orders from all customers
* ✅ Full administrative access

**If Customer authorizes:**
* ✅ Sees only published content
* ✅ Sees only THEIR OWN orders
* ❌ Cannot see drafts or private posts
* ❌ Cannot see other customers' data

**Security note:** The agent is NOT a superuser. It respects WordPress capabilities. Only authorize trusted AI agents with admin accounts.

= What scopes are available? =

Three scope levels:
* `read` - Read-only access (recommended for most use cases)
* `write` - Create and modify content (use with caution)
* `admin` - Administrative operations (only for trusted agents)

**Note:** `admin` includes `write` and `read`. `write` includes `read`.

= How long do tokens last? =

* **Access Token**: 1 hour (3600 seconds)
* **Refresh Token**: 30 days (2,592,000 seconds)
* **Authorization Code**: 10 minutes (600 seconds)

= What happens if my token expires? =

Use the refresh token to get a new access token without requiring user re-authorization. See "Step 5: Refresh Token" in the Getting Started section.

= Can I revoke access? =

Yes. Go to **AI Connect → OAuth Clients** and delete the client. This immediately revokes all tokens issued to that client.

= Is this compatible with WooCommerce? =

The free version includes basic WordPress tools. For full WooCommerce integration (products, cart, orders, customers), check out AI Connect Pro.

= How is this different from the WordPress REST API? =

AI Connect adds:
* OAuth 2.0 authentication (WordPress core uses application passwords)
* WebMCP protocol support (standardized AI integration)
* Rate limiting (protect your site from abuse)
* AI-optimized tool definitions (better AI agent compatibility)
* Extensible module system (easy to add integrations)

= Can I use this in production? =

Yes! AI Connect is production-ready with:
* Enterprise-grade OAuth 2.0 + JWT authentication
* Rate limiting to prevent abuse
* Comprehensive error handling
* Sanitized inputs and outputs
* Regular security updates

= How do I troubleshoot OAuth errors? =

Common issues:
* **"Invalid client credentials"** - Check your Client ID and Secret are correct
* **"Redirect URI mismatch"** - Ensure the redirect_uri parameter exactly matches the one registered
* **"Token expired"** - Use the refresh token to get a new access token
* **"Rate limit exceeded"** - Wait for the retry period or increase limits in settings

Enable WordPress debug mode (`WP_DEBUG`) and check `wp-content/debug.log` for detailed error messages.

== Screenshots ==

1. AI Connect Dashboard - Environment status and quick links
2. OAuth Client Creation - Generate credentials for AI agents
3. Settings Page - Configure rate limits and performance options
4. WebMCP Manifest - Auto-generated tool definitions for AI discovery
5. API Response Example - Successful tool execution with JSON response

== Changelog ==

= 0.1.0 - 2026-02-10 =
* Initial release
* WordPress Core tools: searchPosts, getPost, searchPages, getPage, getCurrentUser
* OAuth 2.0 authentication with JWT tokens
* Rate limiting with Redis/transients support
* WooCommerce module (Pro tier): searchProducts, getProduct, addToCart, getCart, getOrders
* Admin UI for client management and settings
* WebMCP 1.0 manifest generation

== Upgrade Notice ==

= 0.1.0 =
Initial release. Install and configure OAuth clients to enable AI agent access.

== Developer Documentation ==

= API Endpoints =

**Infrastructure:**
* `GET /wp-json/ai-connect/v1/status` - Plugin status and health check
* `GET /wp-json/ai-connect/v1/manifest` - WebMCP manifest with all tools

**OAuth 2.0:**
* `GET /wp-json/ai-connect/v1/oauth/authorize` - Authorization endpoint
* `POST /wp-json/ai-connect/v1/oauth/token` - Token exchange and refresh
* All endpoints use `application/json` content type

**Tools:**
* `POST /wp-json/ai-connect/v1/tools/{tool_name}` - Execute any registered tool
* Requires `Authorization: Bearer {access_token}` header

= Creating Custom Tools =

Register custom tools using the `ai_connect_register_tools` action:

`add_action('ai_connect_register_tools', function($manifest) {
    $manifest->register_tool('mysite.getStats', [
        'description' => 'Get website statistics',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'include_drafts' => [
                    'type' => 'boolean',
                    'description' => 'Include draft posts',
                    'default' => false
                ]
            ]
        ]
    ]);
});`

Handle tool execution with the `ai_connect_handle_tool` filter:

`add_filter('ai_connect_handle_tool', function($result, $tool_name, $args) {
    if ($tool_name === 'mysite.getStats') {
        return [
            'posts' => wp_count_posts()->publish,
            'pages' => wp_count_posts('page')->publish,
            'users' => count_users()['total_users']
        ];
    }
    return $result;
}, 10, 3);`

= Available Filters =

**Authentication:**
* `ai_connect_token_expiry` - Access token lifetime in seconds (default: 3600)
* `ai_connect_refresh_token_expiry` - Refresh token lifetime (default: 2592000)

**Rate Limiting:**
* `ai_connect_rate_limit_per_minute` - Requests per minute (default: 50)
* `ai_connect_rate_limit_per_hour` - Requests per hour (default: 1000)

**Custom Behavior:**
* `ai_connect_handle_tool` - Custom tool execution logic
* `ai_connect_register_tools` - Register additional tools

= Example: Custom Comment Tool =

Complete working example:

`// Register tool
add_action('ai_connect_register_tools', function($manifest) {
    $manifest->register_tool('mysite.getLatestComments', [
        'description' => 'Get latest approved comments',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Number of comments',
                    'default' => 10,
                    'minimum' => 1,
                    'maximum' => 100
                ]
            ]
        ]
    ]);
});

// Handle execution
add_filter('ai_connect_handle_tool', function($result, $tool_name, $args) {
    if ($tool_name !== 'mysite.getLatestComments') {
        return $result;
    }
    
    $limit = isset($args['limit']) ? absint($args['limit']) : 10;
    $comments = get_comments([
        'status' => 'approve',
        'number' => $limit,
        'orderby' => 'comment_date',
        'order' => 'DESC'
    ]);
    
    return array_map(function($comment) {
        return [
            'id' => $comment->comment_ID,
            'author' => $comment->comment_author,
            'content' => $comment->comment_content,
            'date' => $comment->comment_date
        ];
    }, $comments);
}, 10, 3);`

= Security Best Practices =

When creating custom tools:

* ✅ Validate all inputs using WordPress sanitization functions
* ✅ Check user capabilities before executing sensitive operations
* ✅ Limit output size to prevent excessive data transfer
* ✅ Use WordPress escaping functions for output
* ✅ Consider caching for database-heavy operations

= More Information =

Full documentation, examples, and source code available at:
https://github.com/chgold/ai-connect

== Support ==

* **Documentation**: https://github.com/chgold/ai-connect
* **Bug Reports**: https://github.com/chgold/ai-connect/issues
* **Community**: WordPress.org support forums
* **Pro Support**: Available with AI Connect Pro

== Privacy Policy ==

AI Connect does not collect, store, or transmit any personal data from your WordPress site to external services. All API requests are handled locally on your WordPress installation.

OAuth access tokens are stored securely as WordPress transients and automatically expire after 1 hour. Refresh tokens expire after 30 days.

**Data Stored Locally:**
* Client ID (random generated string)
* Client Secret (hashed using WordPress password hashing)
* Client Name (provided by you)
* Redirect URI (provided by you)
* Creation timestamp

This data remains on your WordPress installation and is never transmitted to external services.

== Additional Information ==

= Requirements =

* WordPress 6.0 or higher
* PHP 7.4 or higher
* PHP extensions: json, openssl (usually included)
* Optional: Redis (for production rate limiting)

= Links =

* **GitHub**: https://github.com/chgold/ai-connect
* **Documentation**: https://github.com/chgold/ai-connect#readme
* **Issue Tracker**: https://github.com/chgold/ai-connect/issues
* **Pro Version**: https://github.com/chgold/ai-connect-pro

= Contributing =

AI Connect is open source! Contributions welcome on GitHub.

== Credits ==

* Developed by [chgold](https://github.com/chgold)
* JWT implementation: [firebase/php-jwt](https://github.com/firebase/php-jwt)
* Redis client (optional): [predis/predis](https://github.com/predis/predis)
* Built with WebMCP protocol compliance
