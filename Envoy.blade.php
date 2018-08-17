@servers(['localhost' => '127.0.0.1'])

@setup
function logMessage($message) {
  return "echo '\033[32m" .$message. "\033[0m';\n";
}
@endsetup

@task('build')
  cd '/Users/sebastienlavoie/Code/translit-website'

  {{ logMessage("Building assets for production...") }}
  {{-- npm run prod --}}
  {{ logMessage("Saving index.html...") }}
  rm public/index.html
  open 'http://translit-website.test/build'
  {{ logMessage("Build is finished!") }}
  {{ logMessage("Now just push to production :)") }}
@endtask
