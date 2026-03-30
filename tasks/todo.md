# Move Inline PHP from Blade Templates to Controllers

## Todo
- [x] 1. MenuController + menu.blade.php
- [x] 2. GalleryController + gallery.blade.php
- [x] 3. ReviewsController + reviews.blade.php
- [x] 4. ShowGiftCardsController + gift-cards.blade.php
- [x] 5. LoyaltyController + loyalty.blade.php (both show and store)
- [x] 6. CateringController + catering.blade.php
- [x] 7. ReviewController + submit-review.blade.php
- [x] 8. SurveyController + survey.blade.php
- [x] 9. AboutController + about.blade.php
- [x] 10. ContactController + contact.blade.php
- [x] 11. TrackingController + order-tracking.blade.php
- [x] 12. OrderController (index) + order.blade.php
- [x] 13. OrderController (show) + order-confirmation.blade.php
- [x] Run php -l on all modified files
- [x] Run pint --dirty
- [x] Run tests

## Review
- Moved all @php blocks from 13 Blade templates into their corresponding controllers
- Controllers now compute hero image URLs, page content settings, and derived values
- Blade templates receive pre-computed variables via compact() or array syntax
- Added Storage facade import to controllers that compute Storage::url()
- All templates retain their HTML/Blade directives unchanged
- Tests pass after changes
