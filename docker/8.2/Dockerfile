FROM ubuntu:22.04

LABEL maintainer="Taylor Otwell"

ARG WWWGROUP
ARG NODE_VERSION=18
ARG POSTGRES_VERSION=14

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN sed -i 's|http://archive.ubuntu.com|http://sft.if.usp.br/ubuntu/|g' /etc/apt/sources.list

# Update the sources first
RUN apt-get update && \
    apt-get install -y gnupg curl

# Add necessary keys and repositories
RUN curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c' | gpg --dearmor | tee /usr/share/keyrings/ppa_ondrej_php.gpg > /dev/null && \
    echo "deb [signed-by=/usr/share/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu jammy main" > /etc/apt/sources.list.d/ppa_ondrej_php.list && \
    curl -sLS https://deb.nodesource.com/setup_$NODE_VERSION.x | bash - && \
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarn.gpg >/dev/null && \
    echo "deb [signed-by=/usr/share/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list && \
    curl -sS https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /usr/share/keyrings/pgdg.gpg >/dev/null && \
    echo "deb [signed-by=/usr/share/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt jammy-pgdg main" > /etc/apt/sources.list.d/pgdg.list

# Install all the necessary packages at once
RUN apt-get update && \
    apt-get install -y gosu ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python3 dnsutils \
    php8.2-cli php8.2-dev php8.2-pgsql php8.2-sqlite3 php8.2-gd php8.2-curl php8.2-imap php8.2-mysql php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-soap php8.2-intl php8.2-readline php8.2-ldap php8.2-msgpack php8.2-igbinary php8.2-redis php8.2-swoole php8.2-memcached php8.2-pcov php8.2-xdebug ffmpeg python3-pip nodejs mysql-client postgresql-client-$POSTGRES_VERSION yarn && \
    apt-get -y autoremove && apt-get clean

# Install global npm and other utilities
RUN npm install -g npm && \
    php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer && \
    curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp && chmod a+rx /usr/local/bin/yt-dlp && yt-dlp -U

# Install python packages
RUN ln -s /usr/bin/python3 /usr/local/bin/python && \
    pip install tiktoken requests beautifulsoup4 python-dotenv

# Clean up

RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*



# RUN apt-get update \
#     && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python3 dnsutils \
#     && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c' | gpg --dearmor | tee /usr/share/keyrings/ppa_ondrej_php.gpg > /dev/null \
#     && echo "deb [signed-by=/usr/share/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu jammy main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
#     && apt-get update \
#     && apt-get install -y php8.2-cli php8.2-dev \
#        php8.2-pgsql php8.2-sqlite3 php8.2-gd \
#        php8.2-curl \
#        php8.2-imap php8.2-mysql php8.2-mbstring \
#        php8.2-xml php8.2-zip php8.2-bcmath php8.2-soap \
#        php8.2-intl php8.2-readline \
#        php8.2-ldap \
#        php8.2-msgpack php8.2-igbinary php8.2-redis php8.2-swoole \
#        php8.2-memcached php8.2-pcov php8.2-xdebug ffmpeg \
#        python3-pip \
#     && php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
#     && curl -sLS https://deb.nodesource.com/setup_$NODE_VERSION.x | bash - \
#     && apt-get install -y nodejs \
#     && npm install -g npm \
#     && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarn.gpg >/dev/null \
#     && echo "deb [signed-by=/usr/share/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
#     && curl -sS https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /usr/share/keyrings/pgdg.gpg >/dev/null \
#     && echo "deb [signed-by=/usr/share/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt jammy-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
#     && apt-get update \
#     && apt-get install -y yarn \
#     && apt-get install -y mysql-client \
#     && apt-get install -y postgresql-client-$POSTGRES_VERSION \
#     && apt-get -y autoremove \
#     && apt-get clean \
#     && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
#     && curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
#     && chmod a+rx /usr/local/bin/yt-dlp \
#     && yt-dlp -U \
#     && ln -s /usr/bin/python3 /usr/local/bin/python \
#     && pip install tiktoken requests beautifulsoup4 embedchain python-dotenv


RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.2

RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.2/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

ENTRYPOINT ["start-container"]
