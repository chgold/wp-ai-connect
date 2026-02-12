=== AI Connect - WebMCP Bridge for WordPress ===
Contributors: chgold
Tags: ai, ai-agent, webmcp, oauth, rest-api, artificial-intelligence, chatgpt, claude
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Connect AI agents (ChatGPT, Claude) to your WordPress site with secure OAuth 2.0 authentication.

== Description ==

**AI Connect** enables AI agents to interact with your WordPress content through a secure OAuth 2.0 + JWT authentication system using the WebMCP protocol.

Perfect for AI-powered customer support, automated content analysis, intelligent search, and custom AI integrations.

= ✨ Features =

* **WebMCP Protocol Support** - Industry-standard AI integration
* **OAuth 2.0 + JWT Authentication** - Secure user-delegated access
* **5 WordPress Tools** - Search/get posts, pages, and user info
* **Rate Limiting** - Prevent abuse (50 req/min default)
* **Zero Configuration** - Works out of the box
* **Extensible** - Add custom tools via developer hooks
* **47 Tests Included** - Verified and production-ready

= 🎯 Quick Start for AI Users =

**Using ChatGPT or Claude?**

Tell your AI agent:
> "I want to connect you to my WordPress site at https://mysite.com using AI Connect plugin. The manifest is at /wp-json/ai-connect/v1/manifest"

= 🛠️ Available Tools =

1. **wordpress.searchPosts** - Search posts with filters
2. **wordpress.getPost** - Get single post by ID or slug
3. **wordpress.searchPages** - Search pages
4. **wordpress.getPage** - Get single page by ID or slug
5. **wordpress.getCurrentUser** - Get authenticated user info

= 🔒 How Authentication Works =

**The AI agent operates as the user who authorized it.**

When a site owner authorizes an AI agent:
* The agent receives a token linked to that user's ID
* All API requests run with that user's permissions
* The agent respects WordPress user capabilities

**Examples:**

**If Administrator authorizes:**
* ✅ Sees all posts (including drafts, private)
* ✅ Full access based on admin capabilities

**If Subscriber authorizes:**
* ✅ Sees only published content
* ❌ Cannot see drafts or private content

**Note:** All API calls require OAuth authentication, even for reading public content. This prevents abuse and enables rate limiting.

= 🗺️ Future Development =

We're actively working on new features and improvements!

**We want your feedback:**
* 💡 What features do you need most?
* 🐛 Found a bug? Let us know!

**How to provide feedback:**
* GitHub: https://github.com/chgold/wp-ai-connect/issues/new
* WordPress.org: Support forum

Your feedback directly influences what we build next!

== Installation ==

= Automatic Installation =

1. Go to **Plugins → Add New** in WordPress admin
2. Search for "AI Connect"
3. Click **Install Now** and then **Activate**

= Manual Installation =

1. Download the plugin zip file
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the zip file and click **Install Now**
4. Activate the plugin

= Setup =

1. Go to **AI Connect → OAuth Clients** in WordPress admin
2. Click **Create New OAuth Client**
3. Enter client name and redirect URI
4. Save the **Client ID** and **Client Secret** (shown only once!)

**No configuration needed** - the plugin works immediately!

== Frequently Asked Questions ==

= What is WebMCP? =

WebMCP (Web Model Context Protocol) is a standardized protocol for connecting AI agents to web services. It defines how AI assistants discover, authenticate with, and execute tools on web platforms.

= Does this work with ChatGPT and Claude? =

Yes! AI Connect works with any AI platform that supports OAuth 2.0 authentication and REST APIs. This includes ChatGPT (OpenAI), Claude (Anthropic), Make.com, Zapier, and custom applications.

= Why does reading public content require authentication? =

All API calls require OAuth authentication for security:
* **Rate Limiting** - Prevents spam and abuse
* **Monitoring** - Track who uses your API
* **Security** - Protects against data scraping and DDoS attacks

This is the industry standard (Twitter, GitHub, Google APIs all require auth).

**Exception:** The manifest endpoint is public (no auth needed).

= How does the AI agent authentication work? =

**User-delegated authentication:** The AI agent operates as the WordPress user who authorized it.

When a user authorizes an AI agent:
* The agent receives a token linked to that user's ID
* All API requests run with that user's permissions
* The agent inherits the user's capabilities

**Security:** The agent is NOT a superuser - it respects WordPress user capabilities.

= Is Redis required? =

No, Redis is optional. The plugin works perfectly with WordPress transients. However, Redis is recommended for high-traffic sites (>1,000 requests/day) as it provides better rate limiting performance.

= Can I add custom tools? =

Yes! AI Connect is extensible. Use WordPress hooks to add custom tools:

`add_action('ai_connect_register_tools', function($manifest) {
    $manifest->register_tool('mysite.getStats', [...]);
});`

[See full documentation →](https://github.com/chgold/wp-ai-connect)

= How long do tokens last? =

* **Access Token**: 1 hour (3600 seconds)
* **Refresh Token**: 30 days (2,592,000 seconds)  
* **Authorization Code**: 10 minutes (600 seconds)

Use the refresh token to get a new access token without user re-authorization.

= Can I revoke access? =

Yes. Go to **AI Connect → OAuth Clients** and delete the client. This immediately revokes all tokens issued to that client.

= How do I troubleshoot OAuth errors? =

**Common issues:**

* **"Invalid client credentials"** - Check Client ID and Secret
* **"Token expired"** - Use refresh token to get new access token
* **"Rate limit exceeded"** - Wait for retry period or increase limits
* **REST API 404** - Flush permalinks (Settings → Permalinks → Save)

Enable WordPress debug mode and check `wp-content/debug.log` for details.

= Where can I get support? =

* **Documentation**: https://github.com/chgold/wp-ai-connect
* **Bug Reports**: https://github.com/chgold/wp-ai-connect/issues
* **Community**: WordPress.org support forums

== Screenshots ==

1. Dashboard - System status and quick access to setup
2. OAuth Clients Management - Create and manage credentials
3. Settings - Configure rate limits and options
4. WebMCP Manifest - Auto-generated tool definitions
5. API Response - Example JSON response from API call

== Changelog ==

= 0.1.0 - 2025-02-12 =
* Initial public release
* WebMCP protocol support
* OAuth 2.0 + JWT authentication
* 5 WordPress core tools (searchPosts, getPost, searchPages, getPage, getCurrentUser)
* Rate limiting (Redis + WordPress transients)
* Automatic manifest generation
* 47 comprehensive tests
* Production ready

== Upgrade Notice ==

= 0.1.0 =
Initial release. Install and start connecting AI agents to your WordPress site!

== Feedback & Roadmap ==

**This is an early release (v0.1.0)** and we want your input!

* 💡 **Feature Requests** - What tools do you need?
* 🐛 **Bug Reports** - Found an issue?
* ⭐ **Vote on Features** - Star what you want most

**How to provide feedback:**
* GitHub: https://github.com/chgold/wp-ai-connect/issues/new
* WordPress.org: Support forum

Your feedback directly influences development priorities!

== Privacy Policy ==

AI Connect does not collect, store, or transmit any personal data to external services. All API requests are handled locally on your WordPress installation.

**Data stored locally:**
* OAuth client credentials (hashed)
* Access tokens (temporary, 1 hour expiry)
* Rate limiting counters

No data leaves your WordPress installation.

== Requirements ==

| Component | Required | Notes |
|-----------|----------|-------|
| WordPress | ✅ 6.0+ | Core requirement |
| PHP | ✅ 7.4+ | With json, openssl |
| HTTPS | ⚠️ Production | Required for OAuth |
| Redis | ⭕ Optional | For high traffic |

== Credits ==

* Built with [firebase/php-jwt](https://github.com/firebase/php-jwt)
* Optional [predis/predis](https://github.com/predis/predis) support
* Compliant with WebMCP protocol specification

== Links ==

* [GitHub Repository](https://github.com/chgold/wp-ai-connect)
* [Documentation](https://github.com/chgold/wp-ai-connect/wiki)
* [Issue Tracker](https://github.com/chgold/wp-ai-connect/issues)
* [Roadmap](https://github.com/chgold/wp-ai-connect/issues?q=is%3Aissue+label%3Aroadmap)

---

**Made with ❤️ for the WordPress & AI community**
