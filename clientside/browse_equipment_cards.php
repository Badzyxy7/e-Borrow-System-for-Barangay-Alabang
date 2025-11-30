<?php
// ============================================
// EQUIPMENT CARDS COMPONENT - WITH GROUP REQUEST
// ============================================

if ($result->num_rows > 0):
  while ($row = $result->fetch_assoc()):
    $isAvailable = ($row['status'] == "available" && $row['available'] == 1);
?>
  <div class="equipment-card bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 overflow-hidden flex flex-col relative transition-all">
    
    <!-- Selection Checkbox (Hidden by default, only for available items) -->
    <?php if ($isAvailable): ?>
    <div class="absolute top-3 left-3 z-10 hidden equipment-checkbox-container">
      <input type="checkbox" 
             class="equipment-checkbox w-6 h-6 text-blue-600 rounded-lg focus:ring-2 focus:ring-blue-500 cursor-pointer border-2 border-gray-300 bg-white shadow-sm"
             onchange="toggleEquipmentSelection(<?php echo $row['id']; ?>, this)">
    </div>
    <?php endif; ?>
    
    <?php if (!empty($row['image'])): ?>
      <div class="equipment-image relative overflow-hidden bg-gray-100">
        <img src="../photos/<?php echo htmlspecialchars($row['image']); ?>" 
             class="w-full h-52 sm:h-56 object-cover cursor-pointer"
             alt="<?php echo htmlspecialchars($row['name']); ?>"
             data-equipment-image="../photos/<?php echo htmlspecialchars($row['image']); ?>"
             onclick="openImageModal('<?php echo htmlspecialchars($row['image']); ?>')">
        <div class="absolute top-3 right-3">
          <?php if($isAvailable): ?>
            <span class="equipment-badge-available inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm backdrop-blur-sm bg-green-500/90 text-white">
              <span class="w-1.5 h-1.5 bg-white rounded-full mr-1.5"></span>
              Available
            </span>
          <?php else: ?>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm backdrop-blur-sm bg-yellow-500/90 text-white">
              <span class="w-1.5 h-1.5 bg-white rounded-full mr-1.5"></span>
              Maintenance
            </span>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
    
    <div class="p-5 flex flex-col flex-1">
      <h2 class="text-lg sm:text-xl font-bold mb-2 text-gray-900" data-equipment-name><?php echo htmlspecialchars($row['name']); ?></h2>
      <p class="text-gray-600 mb-4 text-sm sm:text-base line-clamp-2 flex-1" data-equipment-desc><?php echo htmlspecialchars($row['description']); ?></p>
      
      <div class="flex items-center justify-between mb-4 pt-3 border-t border-gray-100">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
          <span class="text-sm font-medium text-gray-700">Quantity: <span class="font-bold text-gray-900" data-equipment-qty><?php echo $row['quantity']; ?></span></span>
        </div>
      </div>

      <?php if ($isAvailable): ?>
        <button onclick="checkProfileAndOpenModal(<?php echo $row['id']; ?>)"
                class="w-full bg-gradient-to-r from-blue-900 to-blue-700 text-white py-3 text-sm sm:text-base rounded-xl hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-sm individual-request-btn">
          Request Equipment
        </button>
      <?php else: ?>
        <button class="w-full bg-gray-300 text-gray-600 py-3 text-sm sm:text-base rounded-xl cursor-not-allowed font-semibold individual-request-btn" disabled>
          Not Available
        </button>
      <?php endif; ?>
    </div>

  </div>

<?php
  endwhile;
else:
?>
  <div class="col-span-full text-center py-16">
    <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
      <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
      </svg>
    </div>
    <h3 class="text-xl font-bold text-gray-700 mb-2">No Equipment Found</h3>
    <p class="text-sm text-gray-500">Try adjusting your search or filters.</p>
  </div>
<?php endif; ?>