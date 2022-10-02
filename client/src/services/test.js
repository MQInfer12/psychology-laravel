import { http } from './htpp';

export const getTests = async () => {
  try {
    const response = await fetch(`${http}test`, {
      method: "GET",
      headers: { "Content-Type": "application/json" }
    });
    return response;
  } catch (error) {
    console.log(error);
  }
};

export const addTest = async (form) => {
  try {
    const response = await fetch(`${http}test`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        nombre: form.nombre,
        descripcion: form.descripcion,
        tiempo: form.tiempo,
      }),
    });
    return response;
  } catch (error) {
    console.log(error);
  }
}

export const getTest = async (id) => {
  try {
    const response = await fetch(`${http}test/${id}`, {
      method: "GET",
      headers: { "Content-Type": "application/json" }
    });
    return response;
  } catch (error) {
    console.log(error);
  }
}

export const updateTest = async (form, id) => {
  try {
    const response = await fetch(`${http}test/${id}`, {
      method: "PUT",
      headers: { 
        "Content-Type": "application/json",
        "accept": "application/json", 
      },
      body: JSON.stringify({
        nombre: form.nombre,
        descripcion: form.descripcion,
        tiempo: form.tiempo,
      }),
    });
    return response;
  } catch (error) {
    console.log(error);
  }
}

export const deleteTest = async (id) => {
  try {
    const response = await fetch(`${http}test/${id}`, {
      method: "DELETE",
      headers: {
        "Content-Type": "application/json",
        "accept": "application/json" 
      },
    });
    return response;
  } catch (error) {
    console.log(error);
  }
}