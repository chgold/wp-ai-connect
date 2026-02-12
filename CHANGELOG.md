# Changelog

All notable changes to AI Connect will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- PHPUnit test suite
- Internationalization (i18n) support
- Freemius SDK integration
- Additional WordPress Core tools (createPost, updatePost, deletePost)
- Advanced WooCommerce tools (updateOrder, createProduct)
- Webhook support
- GraphQL API support

## [0.1.0] - 2025-02-10

### Added
- 🎉 **Initial public release**
- WebMCP protocol support for AI agent integration
- OAuth 2.0 authorization code flow
- JWT-based authentication with access and refresh tokens
- Rate limiting system with Redis support and WordPress transients fallback
- Auto-generated WebMCP manifest endpoint
- WordPress Core module with 5 tools:
  - `wordpress.searchPosts` - Search posts with filters
  - `wordpress.getPost` - Get single post by ID or slug
  - `wordpress.searchPages` - Search pages with filters
  - `wordpress.getPage` - Get single page by ID or slug
  - `wordpress.getCurrentUser` - Get authenticated user info
- WooCommerce module with 5 tools (Pro):
  - `woocommerce.searchProducts` - Search products with advanced filters
  - `woocommerce.getProduct` - Get product by ID or SKU
  - `woocommerce.addToCart` - Add items to cart
  - `woocommerce.getCart` - Get current cart contents
  - `woocommerce.getOrders` - Get customer orders
- Admin UI:
  - Dashboard with system status and OAuth client management
  - Settings page for rate limiting configuration
  - OAuth Clients management page
- Security features:
  - Secure client secret storage
  - Token expiration and refresh mechanism
  - Rate limiting per client
  - Scope-based access control
- Developer features:
  - PSR-4 autoloading
  - Modular architecture for easy extension
  - Comprehensive error handling
  - Detailed logging support

### Technical Details
- **PHP Version**: 7.4 or higher
- **WordPress Version**: 6.0 or higher
- **Dependencies**:
  - firebase/php-jwt ^6.0
  - predis/predis ^2.0
- **Optional**: Redis for production-grade rate limiting

### Documentation
- Complete README with quick start guide
- API documentation for all tools
- Integration examples
- Security best practices
- Troubleshooting guide

### Security
- All OAuth flows follow RFC 6749 specifications
- JWT tokens signed with HS256 algorithm
- State parameter validation for CSRF protection
- Secure client secret generation and storage
- Rate limiting to prevent abuse

---

## Version History Summary

| Version | Date | Description |
|---------|------|-------------|
| 0.1.0 | 2025-02-10 | Initial public release |

---

## Upgrade Notes

### From Pre-Release to 0.1.0

This is the first public release. If you were using a development version:

1. Back up your database
2. Deactivate the old version
3. Delete the old plugin files
4. Install the new version
5. Reactivate the plugin
6. Regenerate OAuth client credentials (old credentials won't work)

---

## Support

- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/chgold/ai-connect/issues)

---

**Legend:**
- ✨ New feature
- 🐛 Bug fix
- 🔒 Security improvement
- 📝 Documentation
- ⚡ Performance improvement
- 💥 Breaking change
- 🗑️ Deprecation
