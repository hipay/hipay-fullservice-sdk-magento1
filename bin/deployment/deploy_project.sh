port=$(wget --no-check-certificate --user=$DOCKER_MACHINE_LOGIN --password=$DOCKER_MACHINE_PASS -qO- https://docker-knock-auth.hipay.org/KyP54YzX/?srvname=deploy.hipay-pos-platform.com)

echo "Deploy project for project $CIRCLE_PROJECT_REPONAME and branch $CIRCLE_BRANCH"
sshpass -p $PASS_DEPLOY ssh root@docker-knock-auth.hipay.org -p $port "cd /deploy/ && ./deploy_project.sh" $CIRCLE_PROJECT_REPONAME $CIRCLE_BRANCH
