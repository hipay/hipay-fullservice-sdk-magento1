#!/bin/bash
BRANCH=$CI_COMMIT_REF_SLUG

echo "Create Artifact project for project $CI_PROJECT_NAME and branch $GITHUB_BRANCH to /deploy/project/artifactory/$CI_PROJECT_NAME/$BRANCH"
docker exec deploy.hipay-pos-platform.com mkdir /deploy/project/artifactory/$CI_PROJECT_NAME/$BRANCH

echo "Transfert Artifact project for project $CI_PROJECT_NAME and branch $GITHUB_BRANCH"
ls package-ready-for-prestashop/
docker cp ./dist/*.tgz deploy.hipay-pos-platform.com:/deploy/project/artifactory/$CI_PROJECT_NAME/$BRANCH

echo "Deploy project in artifactory"
docker exec jira-artifactory-pi.hipay-pos-platform.com /tmp/jfrog rt u /deploy/project/artifactory/$CI_PROJECT_NAME/$BRANCH/*.tgz $CI_PROJECT_NAME/snapshot/ \
    --flat=true --user=admin --password=$ARTIFACTORY_PASSWORD --url http://localhost:8081/artifactory/hipay/
