<nav class="navbar navbar-expand-sm navbar-toggleable-sm navbar-dark c border-bottom box-shadow mb-3">
  <div class="container">
    <div class="navbar-brand" href=".">Infinite skills</div>
    <div class="navbar-collapse collapse d-sm-inline-flex flex-sm-row-reverse">
        <ul class="navbar-nav flex-grow-1">
            <li class="nav-item">
              <a class="nav-link bg-transparent <?php if($NavActive == "Acceuil") echo "active disabled" ; ?>" href="./home">Acceuil<a/>
            </li>
            <li class="nav-item dropdown">
              <input type="button" class="nav-link bg-transparent dropdown-toggle" value="Themes" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"/>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item" href="#">High tech</a>
                <a class="dropdown-item" href="#">Do it yourself</a>
                <a class="dropdown-item" href="#">Art</a>
                <a class="dropdown-item" href="#">Cuisine</a>
                <div class="dropdown-divider"></div>
              </div>
            </li>
            <li class="nav-item w-100">
              <input type="text" class="nav-link w-100" placeholder="Rechercher"/>
            </li>
            <li>
              <a class="nav-link bg-transparent <?php if($NavActive == "Connection") echo "active disabled" ; ?>" href="./connection">Connexion<a/>
            </li>
        </ul>
    </div>
  </div>
</nav>
