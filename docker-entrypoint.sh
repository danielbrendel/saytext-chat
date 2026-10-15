#!/bin/bash

# Default values for environment variables
DEFAULT_APP_DEBUG=true
DEFAULT_APP_UPDATEDEPS=false
DEFAULT_APP_TIMEZONE="UTC"
DEFAULT_APP_SERVERNAME=""
DEFAULT_APP_SERVERTOPIC=""
DEFAULT_APP_SERVERPREVIEW=""
DEFAULT_APP_DELAY_FETCH=10000
DEFAULT_APP_DELAY_ONLINE=10000
DEFAULT_APP_RESOURCEHOST=""
DEFAULT_APP_SERVERLISTHOST=""
DEFAULT_APP_TOKENFILTERS=""
DEFAULT_LOG_ENABLE=true

# Use environment variables if provided, otherwise use defaults
APP_DEBUG=${APP_DEBUG:-$DEFAULT_APP_DEBUG}
APP_UPDATEDEPS="${APP_UPDATEDEPS:-$DEFAULT_APP_UPDATEDEPS}"
APP_TIMEZONE="${APP_TIMEZONE:-$DEFAULT_APP_TIMEZONE}"
APP_SERVERNAME="${APP_SERVERNAME:-$DEFAULT_APP_SERVERNAME}"
APP_SERVERTOPIC="${APP_SERVERTOPIC:-$DEFAULT_APP_SERVERTOPIC}"
APP_SERVERPREVIEW="${APP_SERVERPREVIEW:-$DEFAULT_APP_SERVERPREVIEW}"
APP_DELAY_FETCH=${APP_DELAY_FETCH:-$DEFAULT_APP_DELAY_FETCH}
APP_DELAY_ONLINE=${APP_DELAY_ONLINE:-$DEFAULT_APP_DELAY_ONLINE}
APP_RESOURCEHOST="${APP_RESOURCEHOST:-$DEFAULT_APP_RESOURCEHOST}"
APP_SERVERLISTHOST="${APP_SERVERLISTHOST:-$DEFAULT_APP_SERVERLISTHOST}"
APP_TOKENFILTERS="""${APP_TOKENFILTERS:-$DEFAULT_APP_TOKENFILTERS}"
LOG_ENABLE=${LOG_ENABLE:-$DEFAULT_LOG_ENABLE}

# Function to set the desired timezone
configure_timezone() {
    ln -sf /usr/share/zoneinfo/$APP_TIMEZONE /etc/localtime
    echo "$APP_TIMEZONE" > /etc/timezone
    dpkg-reconfigure -f noninteractive tzdata
}

# Function to set PHP error reporting based on APP_DEBUG
configure_php_error_reporting() {
    if [ "$APP_DEBUG" = "false" ]; then
        # Suppress warnings and notices if APP_DEBUG is false
        echo 'error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED' > /usr/local/etc/php/conf.d/errors.ini
        echo 'display_errors = Off' >> /usr/local/etc/php/conf.d/errors.ini
    else
        # Show all errors if APP_DEBUG is true
        echo 'error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT' > /usr/local/etc/php/conf.d/errors.ini
        echo 'display_errors = On' >> /usr/local/etc/php/conf.d/errors.ini
    fi
}

# Function to create the .env file
create_environment_file() {
    rm -f "/var/www/html/.env"

    cat <<-EOF >> /var/www/html/.env

    # App settings
    APP_NAME="SayText Chat"
    APP_VERSION=1.0
    APP_AUTHOR="Daniel Brendel"
    APP_CONTACT="daniel@danielbrendel.com"
    APP_DEBUG=$APP_DEBUG
    APP_BASEDIR=""
    APP_TIMEZONE="$APP_TIMEZONE"
    APP_SERVERNAME="$APP_SERVERNAME"
    APP_SERVERTOPIC="$APP_SERVERTOPIC"
    APP_SERVERPREVIEW="$APP_SERVERPREVIEW"
    APP_DELAY_FETCH=$APP_DELAY_FETCH
    APP_DELAY_ONLINE=$APP_DELAY_ONLINE
    APP_RESOURCEHOST="$APP_RESOURCEHOST"
    APP_SERVERLISTHOST="$APP_SERVERLISTHOST"

    # Session
    SESSION_ENABLE=true
    SESSION_DURATION=999999
    SESSION_NAME=null

    # Database settings
    DB_ENABLE=true
    DB_HOST="$DB_HOST"
    DB_USER="$DB_USERNAME"
    DB_PASSWORD="$DB_PASSWORD"
    DB_PORT=$DB_PORT
    DB_DATABASE="$DB_DATABASE"
    DB_DRIVER=mysql
    DB_CHARSET="$DB_CHARSET"

    # Logging
    LOG_ENABLE=$LOG_ENABLE
EOF
}

set_apache_server_name() {
    if [ -n "$APACHE_SERVER_NAME" ]; then
        echo "ServerName $APACHE_SERVER_NAME" >> /etc/apache2/apache2.conf;
    fi
}

# Function to check DB connection
check_db() {
    mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" -h "$DB_HOST" -P "$DB_PORT" -D "$DB_DATABASE" -N -s -e "SELECT 1;" > /dev/null 2>&1
}

# Function to wait for the database
wait_for_db() {
    local delay=5  # delay in seconds
    local attempt=1

    while ! check_db; do
        echo "Waiting for database to be available... Attempt $attempt"
        attempt=$((attempt+1))
        sleep "$delay"
    done

    echo "Database is available."
}

# Function to download and extract asset package
install_asset_package() {
    local package=$1
    local destination=$2

    local checkcnt=$(find $(pwd)/public/$destination -maxdepth 1 -type f | wc -l)
    if [ "$checkcnt" -le 1 ]; then
        echo "Downloading and extracting $package..."

        wget -O $package "$APP_RESOURCEHOST/$package"
        unzip $package -d $(pwd)/public/$destination
    fi
}

# Function to download all assets if not already exist
download_assets() {
    if [ -n "$APP_RESOURCEHOST" ]; then
        install_asset_package "assets_backgrounds.zip" "img/backgrounds"
        install_asset_package "assets_icons.zip" "img/icons"
        install_asset_package "assets_sounds.zip" "snd"
    fi
}

add_filter_tokens() {
    while IFS= read -r line; do
  
    if [ -z "$line" ]; then
        continue
    fi

    mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" -h "$DB_HOST" -P "$DB_PORT" -D "$DB_DATABASE" -N -s -e "INSERT INTO Filter (expression, type) VALUES ('$line', 'username');" > /dev/null 2>&1
    mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" -h "$DB_HOST" -P "$DB_PORT" -D "$DB_DATABASE" -N -s -e "INSERT INTO Filter (expression, type) VALUES ('$line', 'message');" > /dev/null 2>&1

    done <<< "$APP_TOKENFILTERS"
}

# Configure timezone
configure_timezone

# Configure PHP error reporting
configure_php_error_reporting

# Create .env configuration file
create_environment_file

# To get rid of apache warnings, you can set the server name with the env var.
set_apache_server_name

# Call the wait_for_db function
wait_for_db

# Copy migration content
cp /tmp/migrations/* /var/www/html/app/migrations

# Set permissions to folder for migrations
chown -R www-data:www-data /var/www/html/app/migrations

# Update dependencies if desired
if [ "$APP_UPDATEDEPS" = "true" ]; then
    composer update
fi

# Run database migrations
php asatru migrate:fresh

# Add filter tokens to table
add_filter_tokens

# Set permissions to folder for logs
chown -R www-data:www-data /var/www/html/app/logs

# Set permissions to public folder
chown -R www-data:www-data /var/www/html/public
chmod 755 /var/www/html/public

# Download assets if not already exists
download_assets

# Print informational message
echo -e "\033[32mThe system is now ready for operation.\033[39m"

# Then exec the container's main process (CMD)
exec "$@"
