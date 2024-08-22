FROM centos:7

# Update repository URL. 
COPY ./.docker/mysql/CentOS-Base.repo /etc/yum.repos.d/CentOS-Base.repo

# Update package and install MySQL.
RUN yum update -y && \
    yum install -y epel-release

# Install Percona MySQL 5.7.
RUN yum install -y https://repo.percona.com/yum/percona-release-latest.noarch.rpm && \
    yum install -y Percona-Server-server-57 wget bzip2 && \
    yum -y update

# Copy MySQL configuration file.
COPY ./.docker/mysql/my.cnf /etc/my.cnf

# Clean and run.
RUN yum clean all
COPY --chown=mysql:mysql ./.docker/mysql/docker-entrypoint.sh /docker-entrypoint.sh
COPY --chown=mysql:mysql ./.docker/mysql/init.sh /tmp/init.sh

RUN mkdir -p /docker-entrypoint-initdb.d/ && \
    touch /docker-entrypoint-initdb.d/init.sql && \
    chmod 777 /docker-entrypoint-initdb.d && \
    chmod 777 /docker-entrypoint-initdb.d/init.sql && \
    chmod +x /docker-entrypoint.sh && \
    chmod +x /tmp/init.sh

USER mysql
