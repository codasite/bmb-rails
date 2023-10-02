import React, { useEffect } from "react";
import { Modal } from "./Modal";

export const ModalWithClickHandlers = (props: {
  buttonClassName: string;
  onButtonClick: (e: HTMLButtonElement) => void;
  show: boolean;
  setShow: (show: boolean) => void;
  children: React.ReactNode;
}) => {
  const handleButtonClick = (e: any) => {
    props.onButtonClick(e.currentTarget);
    props.setShow(true);
  };
  useEffect(() => {
    const buttons = document.getElementsByClassName(props.buttonClassName);
    for (const button of buttons) {
      button.addEventListener("click", handleButtonClick);
    }
    return () => {
      for (const button of buttons) {
        button.removeEventListener("click", handleButtonClick);
      }
    };
  });
  return (
    <Modal show={props.show} setShow={props.setShow}>
      {props.children}
    </Modal>
  );
};
