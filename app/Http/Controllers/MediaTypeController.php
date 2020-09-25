<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\MediaType;

class MediaTypeController extends Controller
{
     public function showmass(){
        //mostrar vista de carga masiva 
        return view('media-types.insert-mass');
     }
  
     public function storemass(Request $r){
      $repetidos=[];
       //arreglo de mediatypes repetidos en bd
       $repetidos=[];

            
            $reglas = [
               'media-types' => 'required|mimes:csv,txt' 
            ];

            //Crear Validador
            $validador = Validator::make($r->all() , $reglas);

            //Validar
            if($validador->fails()){
               //return  $validador->errors()->first('media-types');
               //enviar mensaje de error de validacion a la vista
               return redirect('media-types/insert')->withErrors($validador);
            }else{
               //Trasladar el archivo cargado a Storage
               $r->file('media-types')->storeAs('media-types' , $r->file('media-types')->getClientOriginalName());

               //Ruta completa del archivo en storage;
               $ruta = base_path(). '\storage\app\media-types\\'.$r->file('media-types')->getClientOriginalName();
               //Abrir el archivo almacenado para lectura:
               if( ($puntero = fopen($ruta ,   'r' )) !== false ){
                  //variable a contar las veces que se insertan
                  $contadora = 0;
                  //recorro cada linea del csv: fgetcsv, utilizando el puntero que representa el archivo
                  while( ($linea = fgetcsv($puntero)) !==false  ){
                    //Buscar el media type en la $linea[0];
                    $conteo = MediaType::where('Name','=', $linea[0])->get()->count();
                    //si no hay registro en meditype que se llamen igual
                    if($conteo==0){
                        //Crear objeto MediaType
                        $m = new MediaType();
                        //asigno el nombre del media type
                        $m->Name = $linea[0];
                        //grabo en sqlite el nuevo media - type
                        $m->save();
                        //aumentar en 1 el contadora
                        $contadora++;
                    } else{//hay registros del mediatypes
                      //agregar una casilla al arreglo repetidos
                      $repetidos[] = $linea[0];
                    }
                     
                  }
                                    
                  //TODO: poner mensaje de grabacion de carga masiva 
                  //en la vista
                  //si hubo repetidos
                  if(count($repetidos)==0){
                    return redirect('media-types/insert')
                           ->with('exito' ,
                                "Carga masiva de medios realizada, Registros ingresados:  $contadora  " );

                  }else{
                    return redirect('media-types/insert')
                           ->with('exito' ,
                                "Carga masiva con las siguientes excepciones:  " )
                                ->with("repetidos", $repetidos);
                  }
                  
                                
               } 

               


            }

          


     }


}