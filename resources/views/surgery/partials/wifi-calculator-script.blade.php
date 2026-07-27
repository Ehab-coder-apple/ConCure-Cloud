<script>
document.addEventListener('DOMContentLoaded', function() {
    const woundSelect = document.querySelector('select[name="wound_assessment[classification][wifi][wound]"]');
    const ischemiaSelect = document.querySelector('select[name="wound_assessment[classification][wifi][ischemia]"]');
    const infectionSelect = document.querySelector('select[name="wound_assessment[classification][wifi][infection]"]');
    const stageSelect = document.querySelector('select[name="wound_assessment[classification][wifi_stage]"]');

    if (!woundSelect || !ischemiaSelect || !infectionSelect || !stageSelect) return;

    // SVS WIfI Clinical Stage Matrix [Wound][Ischemia][foot Infection]
    const wifiStageMatrix = [
        // W0
        [
            [1, 1, 2, 3], // I0: fI0, fI1, fI2, fI3
            [1, 2, 3, 4], // I1
            [2, 2, 3, 4], // I2
            [2, 3, 3, 4]  // I3
        ],
        // W1
        [
            [1, 1, 2, 3], // I0
            [1, 2, 3, 4], // I1
            [2, 3, 4, 4], // I2
            [3, 3, 4, 4]  // I3
        ],
        // W2
        [
            [2, 2, 3, 4], // I0
            [3, 3, 4, 4], // I1
            [3, 4, 4, 4], // I2
            [4, 4, 4, 4]  // I3
        ],
        // W3
        [
            [3, 3, 4, 4], // I0
            [4, 4, 4, 4], // I1
            [4, 4, 4, 4], // I2
            [4, 4, 4, 4]  // I3
        ]
    ];

    function calculateWifiStage() {
        const w = parseInt(woundSelect.value);
        const i = parseInt(ischemiaSelect.value);
        const fi = parseInt(infectionSelect.value);

        if (!isNaN(w) && !isNaN(i) && !isNaN(fi)) {
            const stage = wifiStageMatrix[w][i][fi];
            if (stage) {
                stageSelect.value = stage.toString();
                // Add a subtle flash effect to indicate auto-calculation
                stageSelect.style.transition = 'background-color 0.3s ease';
                stageSelect.style.backgroundColor = '#d4edda';
                setTimeout(() => {
                    stageSelect.style.backgroundColor = '';
                }, 800);
            }
        }
    }

    woundSelect.addEventListener('change', calculateWifiStage);
    ischemiaSelect.addEventListener('change', calculateWifiStage);
    infectionSelect.addEventListener('change', calculateWifiStage);
});
</script>
