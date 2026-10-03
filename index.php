<?php include 'common/header.php'; ?>

<div class="container mx-auto px-4 mt-6">
    <div class="relative w-full overflow-hidden h-44 md:h-64 rounded-xl border border-gray-100 shadow-sm bg-white">
        <div id="slider" class="flex transition-transform duration-500 ease-out h-full">

            <?php
            $sliders = $conn->query("SELECT * FROM sliders");

            if ($sliders && $sliders->num_rows > 0):
                while ($slide = $sliders->fetch_assoc()):
            ?>

                <a
                    href="<?php echo !empty($slide['link']) ? htmlspecialchars($slide['link']) : '#'; ?>"
                    class="min-w-full h-full"
                >
                    <img
                        src="<?php echo htmlspecialchars($slide['image']); ?>"
                        class="w-full h-full object-cover"
                        alt="CODE TOP UP"
                    >
                </a>

            <?php
                endwhile;
            else:
            ?>

                <div class="min-w-full h-full flex items-center justify-center bg-gray-50">
                    <p class="text-gray-400">Add sliders from admin panel</p>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>


<script>
    let idx = 0;
    const slides = document.getElementById('slider');
    const totalSlides = slides.children.length;

    if (totalSlides > 1) {
        setInterval(() => {
            idx = (idx + 1) % totalSlides;
            slides.style.transform =
                `translateX(-${idx * 100}%)`;
        }, 4000);
    }
</script>


<div class="container mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">

        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-gamepad text-blue-500"></i>
            Popular Games
        </h2>

        <a
            href="game.php"
            class="text-xs font-bold text-blue-600 hover:underline"
        >
            View All
        </a>

    </div>


    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">

        <?php
        $games = $conn->query("SELECT * FROM games");

        if ($games && $games->num_rows > 0):

            while ($game = $games->fetch_assoc()):
        ?>

            <a
                href="game_detail.php?id=<?php echo (int)$game['id']; ?>"
                class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:border-blue-400 transition-all duration-200"
            >

                <div class="aspect-square overflow-hidden bg-gray-50">

                    <img
                        src="<?php echo htmlspecialchars($game['image']); ?>"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        alt="<?php echo htmlspecialchars($game['name']); ?>"
                    >

                </div>


                <div class="p-3">

                    <h3 class="font-semibold text-gray-800 text-sm truncate">
                        <?php echo htmlspecialchars($game['name']); ?>
                    </h3>

                    <p class="text-[10px] text-gray-400 mt-1">
                        Instant Delivery
                    </p>


                    <div class="mt-3">

                        <span
                            class="block w-full text-center bg-blue-50 text-blue-600 text-[10px] font-bold py-1.5 rounded-md group-hover:bg-blue-600 group-hover:text-white transition-colors"
                        >
                            BUY NOW
                        </span>

                    </div>

                </div>

            </a>

        <?php
            endwhile;

        else:
        ?>

            <div class="col-span-full text-center py-10 bg-white rounded-xl border border-dashed border-gray-300">

                <i class="fa-solid fa-folder-open text-gray-300 text-3xl mb-2"></i>

                <p class="text-gray-400 text-sm">
                    No games available right now.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>


<?php include 'common/footer.php'; ?>
<?php include 'common/bottom.php'; ?>
