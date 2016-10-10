## Installation

    cd /var/www/html
    git clone git@github.com:iamcal/homespring.cloud.git
    ln -s /var/www/html/homespring.cloud/site.conf /etc/apache2/sites-available/homespring.cloud.conf
    a2ensite homespring.cloud
    service apache2 reload
