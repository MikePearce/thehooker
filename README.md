thehooker
=========

A php based deployer which is controlled by git pushes. Ace.

I wanted a way that I could push my Symfony2 project to a remote git repo on my server and then use the pre- and post-receive hooks to move things around, set permissions and clear caches etc, based on which ever branch I was pushing. I started with a whole bunch of echo'd backticks in the post-receive hook file, but soon needed more flexibility. So, here it is.

## Installation

Both `hooks.php` and the `post-receive` and `pre-receive` files should be in your .git/hooks directory. You should set the permissions correctly too.

	chmod +x post-receive hooks.php post-receive pre-receive

You will also need to get a copy of spyc.php (which is a PHP YAML library), from here: https://github.com/mustangostang/spyc/ and put this in your hooks folder too.

You'll need to setup a remote git repo which you can push to also, I used these instructions here: http://sebduggan.com/blog/deploy-your-website-changes-using-git/

## Boxfile
I signed up for a PaaS from Pagoda.com a while ago and was really impressed with the way their boxfile worked. Simply a YAML file which contained a bunch of infos that the server new what to do with for post-deployment.

So impressed that I copied it.

But my setup isn't quite so impressive. Take a look at the boxfile in this repo, you should be able to work it out. It should live in your project root.

###Config vars

Basically, the `config_vars` section will create vars which are replaced with their name, so I have a var called HASH which is in the `execute` group, meaning it will execute whatever value is in there, then replace any instance of the word HASH in the `steps` below. Variables in the `plain` group are not executed.

###Branches
Below that, you have a `branches` block. This should contain any config_vars specific to the branch you're pushing. You also have the post-receive hook in here. I guess you could add other hooks to make this more extensible, but my remote repo will only ever receive pushes (BTW: you can't do a pre-receive hook in here, as it won't have the YAML file before it tries to run it.)

###Steps
Finally, there are the steps. Theses are the actual bit's of code that get called. You may want to replace what is here.

## Usage
Literally:

	git push <remote-name> <branch-name>

So, if you had a 'uat' block in your Boxfile, when you push the uat branch:

	git push myserver uat

It will run through the steps in branches->uat->post-receive:

