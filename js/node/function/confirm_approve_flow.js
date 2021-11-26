socket.on('confirm_approve_flow', function(flow_id, activity, activity_by) {
    socket.broadcast.emit('confirm_approve_flow', {
        flow_id: flow_id,
        activity: activity,
        activity_by: activity_by
    });
});