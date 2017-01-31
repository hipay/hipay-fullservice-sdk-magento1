# Contributing to the HiPay Enterprise extension for Magento v1

Contributions to the HiPay Fullservice extension for Magento v1 should be made via GitHub [pull
requests][pull-requests] and discussed using
GitHub [issues][issues].

### Before you start

If you would like to make a significant change, please open
an issue to discuss it, in order to minimize duplication of effort.

### Setting up a development environment

For the setting up of your development environment, the only prerequisites are to have **docker** and **[docker-compose][docker-compose]** installed on 
your host machine.

Docker compose is used for orchestration of containers as we need for your complete envrionment.

The complete environment contains  PHP, APACHE, MYSQL and SMTP.
The web container installs and preconfigures a magento 1.9 with the Hipay payment module.

 - After cloning the repositiory, retrieve the branch develop
        
            git checkout develop
 
 - Launch a build of images with the two configuration files in parameters:

            sudo docker-compose -f docker-compose.yml -f docker-compose.dev build --no-cache

 - For an automatic configuration of the Hipay module, fill in your "Hipay credentials" in the file

        .bin/conf/development/env_dev
        
      The following properties have to be filled in are:

        HIPAY_API_USER_TEST= 
        HIPAY_API_PASSWORD_TEST=
        HIPAY_TOKENJS_PUBLICKEY_TEST=
        HIPAY_TOKENJS_USERNAME_TEST=
        HIPAY_SECRET_PASSPHRASE_TEST=

 - If you want to change the url and the port used, plese change the properties (by default the port is 8095).

        MAGENTO_URL=http://localhost:8095/
        PORT_WEB=8095

 - By default two methods of payment will be activated (CB and Hosted), it is quite possible to activate others if necessary.
 
        ACTIVE_METHODS=hipay_cc,hipay_hosted
    
    You can find these codes in payment method templates (Variable $ _code)
    
 -  You can now launch the container with the command: 

        sudo docker-compose -f docker-compose.yml -f docker-compose.dev up
 
When all containers are started you will be able to access the magento via the url defined in MAGENTO_URL.

  - Check if all containers are up with
    
        sudo docker-compose ps
        
       You should have the three following containers with exit 0
       
            smtp-hipay-mg-latest                    : Container MailCatcher ( Intercept email from the container web )
            jira-mg-latest.hipay-pos-platform.com   : Container WEB 
            hipayfullservicesdkmagento1_mysql_1     : Container mysql

By default the urls are:
-   Frontend : [http://localhost:8095][url]
-   Backend : [http://localhost:8095/admin][url-admin].

The sources of the hipay module are available in the src folder, any modifications in these sources will be
reflected in the web container.

Three volumes were created at the root of your project after the launch of the containers
-   data ( Contains mysql lib )
-   log  ( Logs magento)
-   web ( source of magento )

### Mysql

The Docker image does not include a mysql client, so you must use your favorite client to access the magento database.
You can connect by default with the following information:
    
        Hostname:localhost
        Port:3307
        Username:magento
        Password:magento
        
### SMTP

You may access at all mails sent by magento via a mailcatcher container at the url :
    
-   http://localhost:1095

### Testing

The CasperJS tests are located in the /bin/tests directory. Currently, the tests do not cover all
features offered by Hipay, but tests for the frontend and for the backend are present.
 
**[CasperJs][casperjs]** and **[PhantomJs][phamtomjs]** are installed by default in the container, so you can run the tests via an exec docker command.
  

        sudo docker exec -it jira-mg-latest.hipay-pos-platform.com sh /tmp/development/launch-all-tests-docker.sh
        
If CasperJs and PhantomJs are installed on you host, you may launch the tests with:  

        sh /bin/conf/tests/launch-all-tests.sh

Any contributions should pass all tests.

### Making the request

Development takes place against the `develop` branch of this repository and pull
requests should be opened against that branch.

### Licensing

The HiPay Fullservice extension for Magento v1 is released under the [Apache
2.0][project-license] license. Any code you submit will be
released under that license.

[project-license]: LICENSE.md

[pull-requests]: https://github.com/hipay/hipay-fullservice-sdk-magento1/pulls
[issues]: https://github.com/hipay/hipay-fullservice-sdk-magento1/issues
[casperjs]: http://casperjs.org/
[url]: http://localhost:8095/
[url-admin]: http://localhost:8095/admin
[phamtomjs]: http://phantomjs.org/
[docker-compose]:https://docs.docker.com/compose/install/
