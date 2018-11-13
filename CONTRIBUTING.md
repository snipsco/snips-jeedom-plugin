How to Contribute
=================

Contributions are welcome! Not familiar with the codebase yet? No problem!
There are many ways to contribute to open source projects: reporting bugs,
helping with the documentation, spreading the word and of course, adding
new features and patches.

Getting Started
---------------
* Make sure you have a GitHub account.
* Open a [new issue](https://github.com/snipsco/snipsmanager/issues), assuming one does not already exist.
* Clearly describe the issue including steps to reproduce when it is a bug.

Making Changes
--------------
* Fork this repository.
* Create a feature branch from where you want to base your work.
* Make commits of logical units (if needed rebase your feature branch before
  submitting it).
* Check for unnecessary whitespace with ``git diff --check`` before committing.
* Make sure your commit messages are well formatted.
* If your commit fixes an open issue, reference it in the commit message (f.e. `#15`).
* Run all the tests (if existing) to assure nothing else was accidentally broken.

These guidelines also apply when helping with documentation.

Submitting Changes
------------------
* Push your changes to a feature branch in your fork of the repository.
* Submit a `Pull Request`.
* Wait for maintainer feedback.

## Programming styling

### Function(Method) name:
- Should be composed by one or more English words, which can clearly and simply describe what this function does
- The first word should start with a lowercase letter
- The non-first words should start with a capital letter

Example:

This is the function used to shift light brightness.
```php
function lightBrightnessShift($_json_lights) {}
```

### Local variable name:
- Should be composed by one or more English words, which can clearly and simply describe the usage of this variable
- All the letters should be lower case
- Each word within the name should be separated by an underline sign ('\_')

Example:

This is the variable used to contain all the bindings which are ready to be executed (Condition checking passed).
```php
$bindings_with_correct_condition = array();
```

### Global variable name:
- Should follow the same naming wat with local variables
- All the words should be capital

### Parameter name:
- Should follow the same naming wat with local variables
- Should start with a underline sign ('\_')

Example:

```php
function lightBrightnessShift($_json_lights) {}
```
