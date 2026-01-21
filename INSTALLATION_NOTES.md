# Installation Notes

## pip list --outdated Issue in tgui_install Repository

### Problem

When using the installation script from https://github.com/tacacsgui/tgui_install, the following error occurs:

```
ERROR: List format 'freeze' cannot be used with the --outdated option.
```

### Root Cause

In the `tgui_install` repository (as of January 2026), file `/inc/install.sh` (line 269) contains:

```bash
python3 -m pip list --outdated --format=freeze | grep -v '^\-e' | cut -d = -f 1 | while read line; do \
```

The `--format=freeze` option is incompatible with the `--outdated` option in newer versions of pip.

### Solution

The line should be changed to use the default format instead:

**Before:**
```bash
python3 -m pip list --outdated --format=freeze | grep -v '^\-e' | cut -d = -f 1 | while read line; do \
```

**After:**
```bash
python3 -m pip list --outdated | tail -n +3 | awk '{print $1}' | while read line; do \
```

Or alternatively, remove the `--format=freeze` option:

```bash
python3 -m pip list --outdated | grep -v '^\-e' | cut -d " " -f 1 | tail -n +3 | while read line; do \
```

### Note

This issue is in the **tgui_install** repository, not in the **tacacsgui** repository. The fix needs to be applied in the installation script repository.

## Composer Security Issues - FIXED ✅

### Issues Fixed in This Repository

1. **firebase/php-jwt Security Vulnerability**
   - **Issue:** Version 5.5.1 had a key/algorithm type confusion vulnerability
   - **Fix:** Replaced `tuupola/slim-jwt-auth` with `jimtools/jwt-auth` v1.x, which requires firebase/php-jwt v6.0+
   - **Status:** ✅ Fixed

2. **Abandoned Package: tuupola/slim-jwt-auth**
   - **Issue:** Package is marked as abandoned
   - **Replacement:** jimtools/jwt-auth
   - **Fix:** Updated composer.json to use `jimtools/jwt-auth` v1.x
   - **Status:** ✅ Fixed

3. **Abandoned Package: tightenco/collect**
   - **Issue:** Package is marked as abandoned
   - **Replacement:** illuminate/collections
   - **Fix:** No action needed - it's a transitive dependency, and illuminate/collections is already in use
   - **Status:** ✅ No action needed

4. **Abandoned Package: adldap2/adldap2**
   - **Issue:** Package is marked as abandoned, no replacement suggested
   - **Fix:** Kept as is - required for LDAP authentication functionality
   - **Status:** ⚠️ Acknowledged - no replacement available
