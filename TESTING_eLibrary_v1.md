# eLibrary v1 — Completion Test Checklist

| ID | Test | Expected | Status |
|---|---|---|---|
| TC-08 | Category search | Category options narrow as the user types; selected category still filters books. | Pending runtime |
| TC-09 | Mobile grid | At narrow mobile width, at least two book cards fit per row without horizontal overflow. | Pending runtime |
| TC-10 | Navigation dismissal | Outside click and Escape close navigation/panel menus. | Pending runtime |
| TC-11 | Cover upload | Valid JPG/PNG/WebP accepted; invalid type/size rejected; replacement removes old cover. | Pending runtime |
| TC-12 | Author editing | Updated author name/biography appears consistently. | Pending runtime |
| TC-13 | Email validation/reset page | Invalid email syntax rejected; Forgot Password page renders its documented non-delivery boundary. | Pending runtime |
| TC-14 | Profile/avatar | Member since and avatar persist; role is not shown in profile. | Pending runtime |
| TC-15 | Activity logs | Search/action/date filters return matching records. | Pending runtime |
| TC-16 | Archive lifecycle | Active book archives; archived book restores or permanently deletes; files are cleaned up. | Pending runtime |
| TC-17 | About/reviews | At most six approved real reviews are displayed. | Pending runtime |
| TC-18 | Favourite/bookmark state | Add/remove state is clear and persists after reload. | Pending runtime |
| TC-19 | Password change | Current password required; mismatches rejected; valid change succeeds. | Pending runtime |

## Static evidence

- 70 PHP files scanned with `php -l`.
- 0 PHP syntax errors.
- MySQL client/server was not available in the current build environment, so runtime DB tests remain Pending.
