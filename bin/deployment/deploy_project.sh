#!/bin/bash
echo "\nCreate Artifact project for project $CI_PROJECT_NAME and branch $CI_COMMIT_REF_SLUG to /deploy/project/artifactory/$CI_PROJECT_NAME/$CI_COMMIT_REF_SLUG"
sshpass -p $PASS_DEPLOY ssh root@docker-knock-auth.hipay.org -p $PORT_SSH_DOCKER -o StrictHostKeyChecking=no mkdir /deploy/project/artifactory/$CI_JOB_ID

echo "\nTransfert Artifact project for project $CI_PROJECT_NAME and branch $CI_COMMIT_REF_SLUG"
sshpass -p $PASS_DEPLOY scp -P $PORT_SSH_DOCKER -o StrictHostKeyChecking=no  ./$CI_COMMIT_REF_SLUG/package/*.tgz root@docker-knock-auth.hipay.org:/deploy/project/artifactory/$CI_JOB_ID

echo "\nDeploy project in artifactory"
docker exec $(docker ps | grep common-artifactory| awk '{print $1}')  /tmp/jfrog rt u /deploy/project/artifactory/$CI_JOB_ID/*.tgz $CI_PROJECT_NAME/snapshot/ \
    --flat=true --user=admin --password=$ARTIFACTORY_PASSWORD --url http://localhost:8081/artifactory/hipay/
