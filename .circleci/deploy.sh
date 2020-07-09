version: 2.1
# read https://circleci.com/docs/2.0/env-vars/ to hide env
orbs:
  aws-code-deploy: circleci/aws-code-deploy@0.0.11

workflows:
  deploy_application:
    jobs:
      - aws-code-deploy/deploy:
          application-name: Omtbiz.in
          service-role-arn: myDeploymentGroupRoleARN
          bundle-key: myS3BucketKey
