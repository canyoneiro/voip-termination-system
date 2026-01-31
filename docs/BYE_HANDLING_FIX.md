# BYE Handling Fix - CDR Generation

## Issue Date
2026-01-29

## Problem Description
CDRs were not being generated when calls ended with BYE. The SQL INSERT for CDR creation was failing.

## Root Cause
Kamailio's `sqlops` module returns DATETIME values as Unix timestamps, not datetime strings.

## Solution
Updated `/etc/kamailio/kamailio.cfg` to use `FROM_UNIXTIME()` for timestamp conversion in:
- HANDLE_BYE route
- MANAGE_FAILURE route

## Status
✅ **FIXED** - CDRs are now generated correctly for both answered and failed calls.
