</main>
    
    <footer style="background-color: var(--primary-color); color: white; padding: 2rem; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px; text-align: left;">
                    <h3 style="margin-bottom: 1rem; color: var(--secondary-color);">ZIDKENU</h3>
                    <p>Soluciones Empresariales Integrales</p>
                    <p>Transformando ideas en resultados</p>
                </div>
                
                <div style="flex: 1; min-width: 200px;">
                    <h3 style="margin-bottom: 1rem; color: var(--secondary-color);">Enlaces Rápidos</h3>
                    <ul style="list-style: none;">
                        <li style="margin-bottom: 0.5rem;"><a href="home_screen.php" style="color: white; text-decoration: none;">Inicio</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="about.php" style="color: white; text-decoration: none;">Nosotros</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="contact.php" style="color: white; text-decoration: none;">Contacto</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="privacy.php" style="color: white; text-decoration: none;">Política de Privacidad</a></li>
                    </ul>
                </div>
                
                <div style="flex: 1; min-width: 200px;">
                    <h3 style="margin-bottom: 1rem; color: var(--secondary-color);">Contacto</h3>
                    <p><i class="fas fa-map-marker-alt" style="margin-right: 0.5rem;"></i> Av. Principal 123, Lima</p>
                    <p><i class="fas fa-phone" style="margin-right: 0.5rem;"></i> +51 987 654 321</p>
                    <p><i class="fas fa-envelope" style="margin-right: 0.5rem;"></i> info@zidkenu.com</p>
                </div>
            </div>
            
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <p>&copy; <?= date('Y') ?> Zidkenu - Soluciones Empresariales. Todos los derechos reservados.</p>
                <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1rem;">
                    <a href="#" style="color: white; font-size: 1.2rem;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: white; font-size: 1.2rem;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color: white; font-size: 1.2rem;"><i class="fab fa-linkedin"></i></a>
                    <a href="#" style="color: white; font-size: 1.2rem;"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>
    
    <script>
        // Script para el menú desplegable en móviles
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle del menú en móviles (puedes implementarlo si necesitas responsive)
            const mobileMenuBtn = document.createElement('div');
            mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            mobileMenuBtn.style.display = 'none';
            mobileMenuBtn.style.fontSize = '1.5rem';
            mobileMenuBtn.style.cursor = 'pointer';
            
            const navLinks = document.querySelector('.nav-links');
            const navbar = document.querySelector('.navbar');
            
            function checkScreenSize() {
                if (window.innerWidth < 768) {
                    mobileMenuBtn.style.display = 'block';
                    navLinks.style.display = 'none';
                    navbar.insertBefore(mobileMenuBtn, navLinks);
                } else {
                    mobileMenuBtn.style.display = 'none';
                    navLinks.style.display = 'flex';
                }
            }
            
            mobileMenuBtn.addEventListener('click', function() {
                navLinks.style.display = navLinks.style.display === 'none' ? 'flex' : 'none';
            });
            
            window.addEventListener('resize', checkScreenSize);
            checkScreenSize();
        });
    </script>
</body>
</html>
