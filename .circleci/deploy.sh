orbs:
  aws-code-deploy: circleci/aws-code-deploy@1.0.1
version: 2.1
workflows:
  deploy_application:
    jobs:
      - aws-code-deploy/deploy:
          application-name: Omtbiz.in
          #bundle-bucket: myApplicationS3Bucket
          #bundle-key: myS3BucketKey
          deployment-group: omtbizDeployGroup
          service-role-arn: omtbizEc2Role
