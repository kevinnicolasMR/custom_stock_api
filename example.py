def salud(energia, usuario):
    if energia > 50:
        print("Felicidades " + usuario + ", tenes energia para salir de joda, tu energia es de: " + str(energia) + "%")
        return energia
    else:
        print("Tu energia es demasiado baja! " + str(energia) + "%")
        return energia
    


energia_actual = 90
usuario_nombre = "Ana Laura"

salud(energia_actual, usuario_nombre)
