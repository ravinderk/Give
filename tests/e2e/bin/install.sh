#!/usr/bin/env bash

echo "Give Log: current branch is ${TRAVIS_BRANCH}";
echo "${TRAVIS_PHP_VERSION:0:3}";
echo "${TRAVIS_EVENT_TYPE}"

if [[ ${TRAVIS_PHP_VERSION:0:3} != "5.3" ]] && [ "${TRAVIS_BRANCH}" == 'master' ]; then
	echo 'Give Log: setup and run frontend tests';

	until $(curl --output /dev/null --silent --head --fail http://localhost:8004); do printf '.'; sleep 5; done;
	cd ~/wordpress_data/wp-content/plugins
	git clone -b ${TRAVIS_BRANCH} --single-branch https://github.com/impress-org/give.git
	cd ~/wordpress_data/wp-content/plugins/give/
	docker exec give_wordpress_1 wp plugin activate give
	composer install
	rm -rf ./node_modules package.json .babelrc package-lock.json
	npm cache clean --force
	echo 'Latest package.json file:';
	wget https://raw.githubusercontent.com/ravinderk/Give/master/package.json
	cat package.json
	echo 'Latest .babelrc file:';
	wget https://raw.githubusercontent.com/ravinderk/Give/master/.babelrc
	cat .babelrc
	node --version
	npm --version
	npm install
	echo 'Who needs babel-core';
	npm outdated
	npm ls --only=dev --depth=0
	npx npm-why babel-core
	npm run dev
	npm run test

else
	echo 'Give Log: Stop frontend tests from running on branches other than master';
fi
