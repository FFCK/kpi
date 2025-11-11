/**
 * Event Worker Control - Frontend JavaScript
 * Manages the background worker for automatic cache generation
 */

var statusCheckInterval = null
var monitoringInterval = null

/**
 * Initialize the page
 */
function Init() {
	// Event handlers
	$('#idevent').change(function () {
		window.location.href = "?evt=" + $(this).val()
	})

	$('.btn_date_evt').click(function (e) {
		e.preventDefault()
		$('#date_event').val($(this).attr('data-date'))
		$('#hour_event').val($(this).attr('data-heure'))
	})

	// Worker control buttons
	$('#btn-start-worker').click(function (e) {
		e.preventDefault()
		startWorker()
	})

	$('#btn-stop-worker').click(function (e) {
		e.preventDefault()
		stopWorker()
	})

	$('#btn-pause-worker').click(function (e) {
		e.preventDefault()
		pauseWorker()
	})

	$('#btn-resume-worker').click(function (e) {
		e.preventDefault()
		resumeWorker()
	})

	// Check worker status immediately
	checkWorkerStatus()

	// Start periodic status check (every 5 seconds)
	statusCheckInterval = setInterval(checkWorkerStatus, 5000)
}

/**
 * Check the current worker status
 */
function checkWorkerStatus() {
	$.ajax({
		type: "GET",
		url: "api_worker.php",
		data: { action: 'status' },
		dataType: "json",
		cache: false,
		success: function (response) {
			if (response.status === 'success') {
				updateUI(response.data)
			} else {
				showError('Failed to get worker status')
			}
		},
		error: function (xhr, status, error) {
			console.error('Status check failed:', error)
			updateUINoConfig()
		}
	})
}

/**
 * Start the worker
 */
function startWorker() {
	const data = {
		action: 'start',
		id_event: $('#id_event').val(),
		date_event: $('#date_event').val(),
		hour_event: $('#hour_event').val(),
		offset_event: $('#offset_event').val(),
		pitch_event: $('#pitch_event').val(),
		delay_event: $('#delay_event').val()
	}

	// Validate required fields
	if (!data.id_event || !data.date_event || !data.hour_event) {
		alert('Please select an event, date, and time')
		return
	}

	$.ajax({
		type: "POST",
		url: "api_worker.php",
		data: data,
		dataType: "json",
		cache: false,
		success: function (response) {
			if (response.status === 'success') {
				showSuccess('Worker started successfully!')
				checkWorkerStatus()
				// Start monitoring
				if (!monitoringInterval) {
					monitoringInterval = setInterval(refreshMonitoring, data.delay_event * 1000)
				}
			} else {
				showError('Failed to start worker: ' + response.message)
			}
		},
		error: function (xhr, status, error) {
			showError('Error starting worker: ' + error)
		}
	})
}

/**
 * Stop the worker
 */
function stopWorker() {
	if (!confirm('Are you sure you want to stop the worker?')) {
		return
	}

	$.ajax({
		type: "POST",
		url: "api_worker.php",
		data: { action: 'stop' },
		dataType: "json",
		cache: false,
		success: function (response) {
			if (response.status === 'success') {
				showSuccess('Worker stopped')
				checkWorkerStatus()
				// Stop monitoring
				if (monitoringInterval) {
					clearInterval(monitoringInterval)
					monitoringInterval = null
				}
				$('#info').html('')
			} else {
				showError('Failed to stop worker: ' + response.message)
			}
		},
		error: function (xhr, status, error) {
			showError('Error stopping worker: ' + error)
		}
	})
}

/**
 * Pause the worker
 */
function pauseWorker() {
	$.ajax({
		type: "POST",
		url: "api_worker.php",
		data: { action: 'pause' },
		dataType: "json",
		cache: false,
		success: function (response) {
			if (response.status === 'success') {
				showSuccess('Worker paused')
				checkWorkerStatus()
			} else {
				showError('Failed to pause worker: ' + response.message)
			}
		},
		error: function (xhr, status, error) {
			showError('Error pausing worker: ' + error)
		}
	})
}

/**
 * Resume the worker
 */
function resumeWorker() {
	$.ajax({
		type: "POST",
		url: "api_worker.php",
		data: { action: 'resume' },
		dataType: "json",
		cache: false,
		success: function (response) {
			if (response.status === 'success') {
				showSuccess('Worker resumed')
				checkWorkerStatus()
			} else {
				showError('Failed to resume worker: ' + response.message)
			}
		},
		error: function (xhr, status, error) {
			showError('Error resuming worker: ' + error)
		}
	})
}

/**
 * Update the UI based on worker status
 */
function updateUI(config) {
	if (!config) {
		updateUINoConfig()
		return
	}

	const status = config.status
	const $statusDiv = $('#worker-status')
	const $indicator = $('#status-indicator')
	const $statusText = $('#status-text')
	const $workerInfo = $('#worker-info')

	// Update status display
	$statusDiv.removeClass('running stopped paused').addClass(status)
	$indicator.removeClass('running stopped paused').addClass(status)

	// Update status text
	let statusLabel = status.charAt(0).toUpperCase() + status.slice(1)
	$statusText.text('Worker Status: ' + statusLabel)

	// Update worker info
	let infoHtml = '<strong>Event:</strong> ' + config.id_event + '<br>'
	infoHtml += '<strong>Date:</strong> ' + config.date_event + '<br>'
	infoHtml += '<strong>Time:</strong> ' + config.hour_event + ' (Initial: ' + config.hour_event_initial + ')<br>'
	infoHtml += '<strong>Warm-up:</strong> ' + config.offset_event + ' minutes<br>'
	infoHtml += '<strong>Pitches:</strong> ' + config.pitch_event + '<br>'
	infoHtml += '<strong>Refresh delay:</strong> ' + config.delay_event + ' seconds<br>'
	infoHtml += '<strong>Executions:</strong> ' + config.execution_count + '<br>'

	if (config.last_execution) {
		infoHtml += '<strong>Last execution:</strong> ' + config.last_execution + ' (' + config.seconds_since_last_execution + 's ago)<br>'
		if (!config.is_healthy && status === 'running') {
			infoHtml += '<span style="color: red;">⚠ Warning: Worker may not be running properly</span><br>'
		}
	}

	if (config.error_message) {
		infoHtml += '<span style="color: red;"><strong>Error:</strong> ' + config.error_message + '</span><br>'
	}

	$workerInfo.html(infoHtml)

	// Update button visibility
	if (status === 'running') {
		$('#btn-start-worker').hide()
		$('#btn-pause-worker').show()
		$('#btn-resume-worker').hide()
		$('#btn-stop-worker').show()
		// Load configuration into form
		loadConfigToForm(config)
		// Start monitoring if not already started
		if (!monitoringInterval) {
			monitoringInterval = setInterval(refreshMonitoring, config.delay_event * 1000)
			refreshMonitoring() // Immediate refresh
		}
	} else if (status === 'paused') {
		$('#btn-start-worker').hide()
		$('#btn-pause-worker').hide()
		$('#btn-resume-worker').show()
		$('#btn-stop-worker').show()
		// Stop monitoring
		if (monitoringInterval) {
			clearInterval(monitoringInterval)
			monitoringInterval = null
		}
	} else {
		// stopped
		$('#btn-start-worker').show()
		$('#btn-pause-worker').hide()
		$('#btn-resume-worker').hide()
		$('#btn-stop-worker').hide()
		// Stop monitoring
		if (monitoringInterval) {
			clearInterval(monitoringInterval)
			monitoringInterval = null
		}
	}
}

/**
 * Update UI when no configuration exists
 */
function updateUINoConfig() {
	const $statusDiv = $('#worker-status')
	const $indicator = $('#status-indicator')
	const $statusText = $('#status-text')
	const $workerInfo = $('#worker-info')

	$statusDiv.removeClass('running paused').addClass('stopped')
	$indicator.removeClass('running paused').addClass('stopped')
	$statusText.text('Worker Status: Not configured')
	$workerInfo.html('No worker configuration found. Configure and start the worker below.')

	$('#btn-start-worker').show()
	$('#btn-pause-worker').hide()
	$('#btn-resume-worker').hide()
	$('#btn-stop-worker').hide()
}

/**
 * Load configuration into form fields
 */
function loadConfigToForm(config) {
	$('#id_event').val(config.id_event)
	$('#date_event').val(config.date_event)
	$('#hour_event').val(config.hour_event)
	$('#offset_event').val(config.offset_event)
	$('#pitch_event').val(config.pitch_event)
	$('#delay_event').val(config.delay_event)
}

/**
 * Refresh monitoring display (shows current match status)
 */
function refreshMonitoring() {
	// This mimics the old monitoring behavior, calling ajax_cache_event.php
	// just for display purposes (the actual cache is generated by the worker)
	var param = $('#event_form').serialize()

	$.ajax({
		type: "GET",
		url: "api_worker.php",
		data: { action: 'status' },
		dataType: "json",
		cache: false,
		success: function (response) {
			if (response.status === 'success' && response.data && response.data.status === 'running') {
				// Get the actual cache data for display
				fetchCacheStatus(response.data)
			}
		}
	})
}

/**
 * Fetch cache status for monitoring display
 */
function fetchCacheStatus(workerConfig) {
	// Calculate the current simulated time
	var param = {
		id_event: workerConfig.id_event,
		date_event: workerConfig.date_event,
		hour_event: workerConfig.hour_event,
		offset_event: workerConfig.offset_event,
		pitch_event: workerConfig.pitch_event,
		delay_event: workerConfig.delay_event
	}

	$.ajax({
		type: "GET",
		url: "ajax_cache_event.php",
		data: param,
		dataType: "json",
		cache: false,
		success: function (data) {
			let texte = '<b>Worker is running</b> (executions: ' + workerConfig.execution_count + ')<br>'
			texte += '<small>Last update: ' + workerConfig.last_execution + '</small><br><br>'
			texte += '<table class="table table-bordered table-condensed"><thead>'
			texte += '<tr><th></th><th colspan="3">Current game</th><th colspan="3">Next game</th></tr>'
			texte += '<tr><th>Pitch</th><th>Time</th><th>Num</th><th>Id</th><th>Time</th><th>Num</th><th>Id</th></tr>'
			texte += '</thead><tbody>'
			data.pitches.forEach((item) => {
				texte += '<tr><td>' + item.pitch + '</td>'
				if (item.game) {
					texte += '<td>' + item.time + '</td><td>#' + item.num + '</td><td>' + item.game + '</td>'
				} else {
					texte += '<td colspan="3">Waiting...</td>'
				}
				if (item.next.id != null) {
					texte += '<td>' + item.next.time + '</td><td>#' + item.next.num + '</td><td>' + item.next.id + '</td>'
				} else {
					texte += '<td colspan="3">Waiting...</td>'
				}
				texte += '</tr>'
			})
			texte += '</tbody></table>'
			$('#info').html(texte)
		},
		error: function () {
			$('#info').html('<span style="color: red;">Failed to fetch monitoring data</span>')
		}
	})
}

/**
 * Show success message
 */
function showSuccess(message) {
	console.log('Success:', message)
	// Could add a toast notification here
}

/**
 * Show error message
 */
function showError(message) {
	console.error('Error:', message)
	alert(message)
}

// Initialize on document ready
$(document).ready(function () {
	Init()

	// Set current time as default
	const now = new Date()
	const hours = String(now.getHours()).padStart(2, '0')
	const minutes = String(now.getMinutes()).padStart(2, '0')
	document.getElementById('hour_event').value = `${hours}:${minutes}`
})

// Cleanup on page unload
$(window).on('beforeunload', function () {
	if (statusCheckInterval) {
		clearInterval(statusCheckInterval)
	}
	if (monitoringInterval) {
		clearInterval(monitoringInterval)
	}
})
