www.afam.com website

### Install dependencies. Uses gulp for builds
```
npm install --global gulp-cli
npm install --save-dev gulp
npm install
gulp config-prod
gulp build
```

### To serve
`gulp serve`

### To build for distribution
`gulp build`

### Write a new blog post
`hugo new blog/<blog-post-title>.md`

### TODO
Fix issue with when `gulp serve` is running and there's a change in 
the source, it loses css and js files being loaded. This is because 
the html files are recreated by hugo and causes it to lose the asset fingerprints 
changes in the html files. Workaround is to issue `gulp serve`