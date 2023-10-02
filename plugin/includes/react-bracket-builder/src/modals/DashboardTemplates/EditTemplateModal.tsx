import * as React from "react";
import { useState } from "react";
import { bracketApi } from "../../brackets/shared/api/bracketApi";
import { MyTemplatesModal } from "./MyTemplatesModal";

export const EditTemplateModal = (props: {}) => {
  const [showModal, setShowModal] = useState(false);
  const [templateId, setTemplateId] = useState<number | null>(null);
  const [loading, setLoading] = useState(false);
  const [input, setInput] = useState("");
  const [hasError, setHasError] = useState(false);
  const handleEditTemplateClick = (e: HTMLButtonElement) => {
    const templateId = e.dataset.templateId;
    setInput(e.dataset.templateName);
    setTemplateId(parseInt(templateId));
  };
  const onEditTemplate = () => {
    if (!input) {
      setHasError(true);
      return;
    }
    setLoading(true);
    bracketApi
      .updateTemplate(templateId, {
        title: input,
      })
      .then((res) => {
        window.location.reload();
      })
      .catch((err) => {
        console.error(err);
        setLoading(false);
      });
  };
  return (
    <MyTemplatesModal
      submitButtonText={"Save"}
      onSubmit={onEditTemplate}
      header={"Edit info"}
      input={input}
      setInput={setInput}
      buttonClassName={"wpbb-edit-template-button"}
      onButtonClick={handleEditTemplateClick}
      hasError={hasError}
      setHasError={setHasError}
      loading={loading}
      errorText={"Template name is required"}
      placeholderText={"Template name..."}
    />
  );
};
