// Import JavaScript Functions

document.addEventListener('DOMContentLoaded', function() {
    // Drag and drop functionality for file upload
    const fileUploadArea = document.querySelector('.import-file-upload');
    const fileInput = document.querySelector('input[type="file"]');
    
    if (fileUploadArea && fileInput) {
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        // Highlight drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, unhighlight, false);
        });
        
        // Handle dropped files
        fileUploadArea.addEventListener('drop', handleDrop, false);
        
        // Handle click on drop area
        fileUploadArea.addEventListener('click', function() {
            fileInput.click();
        });
        
        // Handle file selection via input
        fileInput.addEventListener('change', handleFiles);
    }
    
    // Progress bar functionality
    const importForm = document.getElementById('importForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            // Show progress container
            const progressContainer = document.querySelector('.import-progress-container');
            if (progressContainer) {
                progressContainer.style.display = 'block';
            }
            
            // Simulate progress (in a real implementation, this would be updated by the server)
            simulateProgress();
        });
    }
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight() {
    const fileUploadArea = document.querySelector('.import-file-upload');
    if (fileUploadArea) {
        fileUploadArea.classList.add('dragover');
    }
}

function unhighlight() {
    const fileUploadArea = document.querySelector('.import-file-upload');
    if (fileUploadArea) {
        fileUploadArea.classList.remove('dragover');
    }
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles({ target: { files: files } });
}

function handleFiles(e) {
    const files = e.target.files;
    const fileInput = document.querySelector('input[type="file"]');
    const fileUploadArea = document.querySelector('.import-file-upload');
    
    if (files.length > 0 && fileInput && fileUploadArea) {
        // Update file input with selected files
        fileInput.files = files;
        
        // Update UI to show selected files
        const fileList = document.createElement('div');
        fileList.className = 'file-list mt-3';
        
        for (let i = 0; i < files.length; i++) {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.textContent = `${files[i].name} (${formatFileSize(files[i].size)})`;
            fileList.appendChild(fileItem);
        }
        
        // Remove existing file list if present
        const existingFileList = fileUploadArea.querySelector('.file-list');
        if (existingFileList) {
            existingFileList.remove();
        }
        
        fileUploadArea.appendChild(fileList);
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function simulateProgress() {
    const progressBar = document.querySelector('.import-progress-fill');
    if (progressBar) {
        let width = 0;
        const interval = setInterval(function() {
            if (width >= 100) {
                clearInterval(interval);
            } else {
                width++;
                progressBar.style.width = width + '%';
            }
        }, 50);
    }
}

// Function to clear the form
function clearImportForm() {
    const importForm = document.getElementById('importForm');
    if (importForm) {
        importForm.reset();
        
        // Clear file list display
        const fileUploadArea = document.querySelector('.import-file-upload');
        if (fileUploadArea) {
            const fileList = fileUploadArea.querySelector('.file-list');
            if (fileList) {
                fileList.remove();
            }
        }
    }
}