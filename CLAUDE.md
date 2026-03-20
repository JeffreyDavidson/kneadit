# CLAUDE.md — KneadIt Project Rules

## Hard Rules (NEVER break these)

### 1. Verify before you use
- **NEVER** add a `use` statement, trait, or class reference without first confirming it exists in `vendor/`
- Run: `grep -r "ClassName" vendor/ --include='*.php'` before adding ANY Filament class/trait
- If it doesn't exist in vendor, it doesn't exist. Period.

### 2. Lint before you commit
- Pre-commit hook runs `php -l` on all staged PHP files (`.githooks/pre-commit`)
- If the hook isn't catching errors, run manually: `php -l path/to/file.php`
- NEVER force-skip the hook (`--no-verify`)

### 3. Test on server before merging to main
- Push to `develop` first when making risky changes
- SSH to cold-moon and verify: `ssh forge@137.184.194.56 "cd /home/forge/getkneadit.app/current && php artisan tinker --execute='echo 1;'"`
- `main` triggers production deploy — treat it with respect

### 4. Filament 5 specifics
- Form signature: `form(Schema $schema): Schema` — NOT `Form $form`
- **NO** `Filament\Tables\Actions` namespace — use `Filament\Actions\*` for everything
- **NO** `Filament\Forms\Get` / `Filament\Forms\Set` — use `Filament\Schemas\Components\Utilities\Get` and `Set`
- **NO** `HasSlideOverForm` trait — it doesn't exist in Filament 5
- Slide-overs: use `EditAction::make()->slideOver()` on tables + index-only page routes
- BlogPosts are the ONLY resource with dedicated create/edit page routes (full pages, not slide-overs)
- Sections in slide-over forms need `->columnSpanFull()` to fill width

### 5. Don't mass-edit without understanding
- Before changing 40+ files, verify the approach works on ONE file first
- Check vendor source code, not assumptions or memory from older Filament versions
- If unsure, ask Jeffrey rather than guessing

## Project Structure
- Resources: `app/Filament/Resources/{Name}/` with separate `Schemas/`, `Tables/`, `Pages/` dirs
- Some resources (LoyaltyRewards, CustomerPhotos, GalleryPhotos) have inline table config in the Resource file
- Custom CSS: `public/css/filament-custom.css` (cache-busted via `?v=filemtime()`)
- Server: cold-moon (`forge@137.184.194.56`), site at `/home/forge/getkneadit.app/current`

## Git
- Pre-commit hook: `.githooks/pre-commit` (PHP lint)
- Hook path configured via `git config core.hooksPath .githooks`
- Branches: `main` (production deploy) and `develop` (staging)
- Commit format: `type: description` (feat/fix/hotfix/refactor/style/docs/chore/test)
