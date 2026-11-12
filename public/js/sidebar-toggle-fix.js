// Custom sidebar toggle enhancement for REYSCHOOL project
// This file adds responsive sidebar toggle functionality

document.addEventListener('DOMContentLoaded', function () {
  // Enhanced Menu collapse click for desktop and mobile
  var mobile_collapse_btn = document.querySelector('#mobile-collapse');
  
  if (mobile_collapse_btn) {
    // Remove any existing listeners by cloning the node
    var newBtn = mobile_collapse_btn.cloneNode(true);
    mobile_collapse_btn.parentNode.replaceChild(newBtn, mobile_collapse_btn);
    
    // Add new enhanced click listener
    newBtn.addEventListener('click', function (e) {
      e.preventDefault();
      var temp_sidebar = document.querySelector('.pc-sidebar');
      
      if (temp_sidebar) {
        // Check if we're on desktop or mobile (992px is Bootstrap's lg breakpoint)
        var isDesktop = window.innerWidth >= 992;
        
        if (isDesktop) {
          // Desktop: toggle pc-sidebar-hide class for clean minimize/expand
          temp_sidebar.classList.toggle('pc-sidebar-hide');
        } else {
          // Mobile: use overlay sidebar logic
          if (temp_sidebar.classList.contains('mob-sidebar-active')) {
            // Close mobile menu
            temp_sidebar.classList.remove('mob-sidebar-active');
            var overlay = document.querySelector('.pc-menu-overlay');
            if (overlay) {
              overlay.remove();
            }
          } else {
            // Open mobile menu
            temp_sidebar.classList.add('mob-sidebar-active');
            temp_sidebar.insertAdjacentHTML('beforeend', '<div class="pc-menu-overlay"></div>');
            var newOverlay = document.querySelector('.pc-menu-overlay');
            if (newOverlay) {
              newOverlay.addEventListener('click', function () {
                temp_sidebar.classList.remove('mob-sidebar-active');
                newOverlay.remove();
              });
            }
          }
        }
      }
    });
    
    console.log('✅ Sidebar toggle enhancement loaded successfully');
  }
});
