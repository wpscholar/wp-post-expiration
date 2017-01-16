# Post Expiration

A WordPress module that makes it easy to add support for post expiration to any post type.

## Requirements

- PHP 5.4+
- WordPress 4.5+

## Installation

Add the module to your code base via [Composer](https://getcomposer.org/)

```SHELL
composer require wpscholar/wp-post-expirator
```

## Initialization

Add this line to your code to enable the module for use with post type support:

```PHP
add_action( 'init', 'wpscholar\PostExpiration::initialize', 99 );
```

## Adding Post Type Support

If you are adding support to a pre-existing post type, just add this code:

```PHP
add_post_type_support( 'post', 'expiration' );
```

Be sure to replace `post` with the name of your post type.

Or, in the `supports` argument when registering a post type, just add `'expiration'`.

## Available Methods

The following static methods are publicly available:

- `setExpiration( $post_id, $expiration )` - Set expiration for a specific post. Expiration is a Unix timestamp.
- `expirePost( $post_id )` - Immediately expire a specific post.
- `expirePosts()` - Expire all posts. (Limit 100 per run per post type)