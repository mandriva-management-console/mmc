============================
Contributing to MMC with git
============================

MMC source code is hosted on github: https://github.com/mandriva-management-console/mmc

Repo setup
##########

1. Setup an account on github and fork https://github.com/mandriva-management-console/mmc. 

  Then checkout your fork:

  ::

    git clone git@github.com:<USER>/mmc.git

2. Add a remote on mandriva-management-console/mmc

  ::

    git remote add mmc git@github.com:mandriva-management-console/mmc.git

3. Create a local branch that is tracking the main repository

  ::

    git branch master-mmc --track mmc/master


Using pull requests
###################

We use github pull requests to review fixes and new features.

For each bugfix or new feature you will propose a pull request which can
be merge directly in the MMC repository trough the github interface.

This means that you need to create and publish a branch for every fix or
feature then ask a pull of these branches. Thanks to git, this is very easy.

The pull request commits must be clean and atomic.

Fixing a bug or developping a new feature in master
===================================================

1. Update the master-mmc branch to make sure we are working with an up-to-date installation.

  ::

    git checkout master-mmc
    git pull

2. Create a local branch based on master-mmc

  ::

    git checkout -b fix-blah-blah (or feature-blah-blah)

3. Fix the bug with one or two commits (each commit must be atomic)

4. Publish the branch to your github account

  ::

    git-publish-branch (http://git-wt-commit.rubyforge.org/git-publish-branch)

5. Test your fix or feature ! (others can also test it by merging your branch since its public now)

6. If everything is fine, in the github interface, select the fix-blah-blah (or feature-blah-blah) branch and click on pull request. 

Github will try to create a pull request on the master branch by default

In the pull request you can explain what your commit is fixing and how
to reproduce the bug if needed.r


Commit directly in master
#########################

If you have the rights to commit to the main repository and you are sure of
your fix, you might want to commit directly in the main repo directly instead
of creating a pull request.

Use the following procedure to keep a clean history on the main branch 
(ie: no merge commits)

1. Always create a branch for working on your fix

  ::

    git checkout master-mmc
    git pull
    git checkout -b fix-foo

2. Fix what you want to fix, test etc. When ready go to 3.

3. Update master-mmc to get latests commits

  ::

    git checkout master-mmc
    git pull

4. Rebase your branch on top of master-mmc

  ::
    
    git checkout fix-foo
    git rebase master-mmc

  This will apply all the commits you have made in fix-foo on top
  of the latest master-mmc history

5. Then merge your branch on master-mmc and push the changes to the main repository

  ::

    git checkout master-mmc
    git merge fix-foo
    git push

  Since all commits in fix-foo are on top of the master-mmc commits
  thanks to rebase, the merge will be done in fast forward mode and
  there will be no merge commit.
