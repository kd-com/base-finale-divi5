FROM wordpress:latest

# Répertoire WordPress
WORKDIR /var/www/html

# Installation de WP-CLI + dépendances utiles
RUN apt-get update && apt-get install -y \
      less \
      mariadb-client \
      curl \
      sudo \
    && curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

CMD ["apache2-foreground"]