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
				// API now returns array of configs
				updateUI(response.data || [])
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
 * Stop a specific event
 */
function stopEvent(idEvent) {
	if (!confirm('Stop event #' + idEvent + '?')) {
		return
	}

	$.ajax({
		type: "POST",
		url: "api_worker.php",
		data: {
			action: 'stop',
			id_event: idEvent
		},
		dataType: "json",
		cache: false,
		success: function (response) {
			if (response.status === 'success') {
				showSuccess('Event #' + idEvent + ' stopped')
				checkWorkerStatus()
			} else {
				showError('Failed to stop event: ' + response.message)
			}
		},
		error: function (xhr, status, error) {
			showError('Error stopping event: ' + error)
		}
	})
}

/**
 * Pause a specific event
 */
function pauseEvent(idEvent) {
	$.ajax({
		type: "POST",
		url: "api_worker.php",
		data: {
			action: 'pause',
			id_event: idEvent
		},
		dataType: "json",
		cache: false,
		success: function (response) {
			if (response.status === 'success') {
				showSuccess('Event #' + idEvent + ' paused')
				checkWorkerStatus()
			} else {
				showError('Failed to pause event: ' + response.message)
			}
		},
		error: function (xhr, status, error) {
			showError('Error pausing event: ' + error)
		}
	})
}

/**
 * Resume a specific event
 */
function resumeEvent(idEvent) {
	$.ajax({
		type: "POST",
		url: "api_worker.php",
		data: {
			action: 'resume',
			id_event: idEvent
		},
		dataType: "json",
		cache: false,
		success: function (response) {
			if (response.status === 'success') {
				showSuccess('Event #' + idEvent + ' resumed')
				checkWorkerStatus()
			} else {
				showError('Failed to resume event: ' + response.message)
			}
		},
		error: function (xhr, status, error) {
			showError('Error resuming event: ' + error)
		}
	})
}

/**
 * Start a new event in the worker
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
				showSuccess('Event #' + data.id_event + ' started successfully!')
				checkWorkerStatus()
			} else {
				showError('Failed to start event: ' + response.message)
			}
		},
		error: function (xhr, status, error) {
			showError('Error starting event: ' + error)
		}
	})
}

/**
 * Stop all events
 */
function stopWorker() {
	if (!confirm('Are you sure you want to stop ALL events?')) {
		return
	}

	$.ajax({
		type: "POST",
		url: "api_worker.php",
		data: { action: 'stop' }, // No id_event = stop all
		dataType: "json",
		cache: false,
		success: function (response) {
			if (response.status === 'success') {
				showSuccess('All events stopped')
				checkWorkerStatus()
				// Stop monitoring
				if (monitoringInterval) {
					clearInterval(monitoringInterval)
					monitoringInterval = null
				}
				$('#info').html('')
			} else {
				showError('Failed to stop events: ' + response.message)
			}
		},
		error: function (xhr, status, error) {
			showError('Error stopping events: ' + error)
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
 * Update the UI based on worker status (multiple events)
 */
function updateUI(configs) {
	if (!configs || configs.length === 0) {
		updateUINoConfig()
		return
	}

	const $statusDiv = $('#worker-status')
	const $indicator = $('#status-indicator')
	const $statusText = $('#status-text')
	const $workerInfo = $('#worker-info')

	// Determine global status (running if at least one is running)
	const hasRunning = configs.some(c => c.status === 'running')
	const hasPaused = configs.some(c => c.status === 'paused')
	const globalStatus = hasRunning ? 'running' : (hasPaused ? 'paused' : 'stopped')

	// Update status display
	$statusDiv.removeClass('running stopped paused').addClass(globalStatus)
	$indicator.removeClass('running stopped paused').addClass(globalStatus)

	// Update status text
	$statusText.text('Worker Status: ' + configs.length + ' active event(s)')

	// Build event list HTML
	let infoHtml = '<div style="margin-top: 15px;">'

	configs.forEach((config, index) => {
		const statusClass = config.status === 'running' ? 'success' : (config.status === 'paused' ? 'warning' : 'default')
		const statusBadge = '<span class="label label-' + statusClass + '">' + config.status + '</span>'

		infoHtml += '<div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 4px;">'
		infoHtml += '<h5><strong>Event #' + config.id_event + '</strong> ' + statusBadge + '</h5>'
		infoHtml += '<strong>Date:</strong> ' + config.date_event + '<br>'
		infoHtml += '<strong>Current Time:</strong> ' + (config.current_simulated_time || config.hour_event) + ' (Initial: ' + config.hour_event_initial + ')<br>'
		infoHtml += '<strong>Pitches:</strong> ' + config.pitch_event + ' | <strong>Delay:</strong> ' + config.delay_event + 's<br>'
		infoHtml += '<strong>Executions:</strong> ' + config.execution_count

		if (config.last_execution && config.seconds_since_last_execution !== null) {
			let timeAgo = config.seconds_since_last_execution
			if (timeAgo < 60) {
				infoHtml += ' | <strong>Last:</strong> ' + timeAgo + 's ago'
			} else {
				infoHtml += ' | <strong>Last:</strong> ' + Math.floor(timeAgo / 60) + 'm ago'
			}
		}

		infoHtml += '<br>'

		// Individual event controls
		infoHtml += '<div style="margin-top: 5px;">'
		if (config.status === 'running') {
			infoHtml += '<button class="btn btn-xs btn-warning" onclick="pauseEvent(' + config.id_event + ')">⏸ Pause</button> '
			infoHtml += '<button class="btn btn-xs btn-danger" onclick="stopEvent(' + config.id_event + ')">⏹ Stop</button>'
		} else if (config.status === 'paused') {
			infoHtml += '<button class="btn btn-xs btn-info" onclick="resumeEvent(' + config.id_event + ')">▶ Resume</button> '
			infoHtml += '<button class="btn btn-xs btn-danger" onclick="stopEvent(' + config.id_event + ')">⏹ Stop</button>'
		}
		infoHtml += '</div>'

		infoHtml += '</div>'
	})

	infoHtml += '</div>'

	$workerInfo.html(infoHtml)

	// Update main buttons
	$('#btn-start-worker').show()
	if (hasRunning || hasPaused) {
		$('#btn-stop-worker').show().text('⏹ Stop All')
	} else {
		$('#btn-stop-worker').hide()
	}
	$('#btn-pause-worker').hide()
	$('#btn-resume-worker').hide()

	// Start monitoring if at least one event is running
	if (hasRunning && !monitoringInterval) {
		const minDelay = Math.min(...configs.map(c => c.delay_event))
		monitoringInterval = setInterval(refreshMonitoring, minDelay * 1000)
		refreshMonitoring()
	} else if (!hasRunning && monitoringInterval) {
		clearInterval(monitoringInterval)
		monitoringInterval = null
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
