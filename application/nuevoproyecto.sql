CREATE TABLE IF NOT EXISTS producto(
    producto_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
    denominacion VARCHAR(100),
    precio DECIMAL(6,2)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS domicilio( 
    domicilio_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    calle VARCHAR(100),
    numero INT(2), 
    planta INT(2),
    puerta VARCHAR(2),
    ciudad VARCHAR(50) 
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS cliente(
    cliente_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    denominacion VARCHAR(100),
    nif VARCHAR(9),
    domicilio INT(11),
    FOREIGN KEY (domicilio)
        REFERENCES domicilio(domicilio_id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS datodecontacto(
    datodecontacto_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    denominacion VARCHAR(100),
    valor VARCHAR(100),
    cliente INT(11),
    INDEX(cliente),
    FOREIGN KEY (cliente)
        REFERENCES cliente(cliente_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedido(
    pedido_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    estado INT(1),
    fecha DATETIME,
    cliente INT(11),
    INDEX(cliente),
    FOREIGN KEY (cliente)
        REFERENCES cliente(cliente_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS productopedido(
    productopedido_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    compuesto INT(11),
    INDEX (compuesto),
    FOREIGN KEY (compuesto)
        REFERENCES pedido(pedido_id)
        ON DELETE CASCADE,
    compositor INT(11),
    FOREIGN KEY (compositor)
        REFERENCES producto(producto_id)
        ON DELETE CASCADE,
    fm INT(4)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categoria (
    categoria_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    denominacion VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categoriacategoria (
    categoriacategoria_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    compuesto INT(11),
    INDEX(compuesto),
    FOREIGN KEY (compuesto)
        REFERENCES categoria(categoria_id)
        ON DELETE CASCADE,
    compositor INT(11),
    FOREIGN KEY (compositor)
        REFERENCES categoria(categoria_id)
        ON DELETE CASCADE    
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS productocategoria (
    productocategoria_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    compuesto INT(11),
    INDEX(compuesto),
    FOREIGN KEY (compuesto)
        REFERENCES categoria(categoria_id)
        ON DELETE CASCADE,
    compositor INT(11),
    FOREIGN KEY (compositor)
        REFERENCES producto(producto_id)
        ON DELETE CASCADE,    
    fm INT(4)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuario (
    usuario_id VARCHAR(8) NOT NULL PRIMARY KEY,
    denominacion VARCHAR(100),
    nivel INT(1)
) ENGINE=InnoDB;

INSERT IGNORE INTO categoria (categoria_id, denominacion) VALUES (1, ".");
