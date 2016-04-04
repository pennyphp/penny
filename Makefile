docs_clear:
	rm -rf ./site
docs_build:
	docker pull gianarb/mkdocs
	docker run --rm --name penny_docs -v ${PWD}:/project -w /project gianarb/mkdocs
docs_server:
	php -S 0:9567 -t ./site
