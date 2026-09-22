# StudioFlow Postman Collection

## How to use
1. Open Postman.
2. Click **Import** (top-left).
3. Drag in both files from this folder:
   - `StudioFlow-API.postman_collection.json`
   - `StudioFlow-Local.postman_environment.json`
4. In the top-right environment dropdown, select **StudioFlow Local**.
5. Run the `Login` request first (Auth folder) to get a token.
6. Copy the returned token into the `token` variable under
   Environments > StudioFlow Local > Current Value.

## Keeping this in sync
Whenever you add or change an API endpoint:
1. Add/update the corresponding request in Postman.
2. Re-export the collection (see steps below) and overwrite the file in this folder.
3. Commit it in the same PR as the route/controller change.

## Re-exporting after changes
- Collection: hover collection name > `...` > Export > Collection v2.1
- Environment: hover environment name > `...` > Export
- **Before exporting the environment, clear the `token` / `client_token`
  Current Values** — don't commit live tokens.