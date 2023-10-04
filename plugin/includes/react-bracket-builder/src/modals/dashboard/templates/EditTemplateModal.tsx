import * as React from 'react'
import { useState } from 'react'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import { TextFieldModal } from '../../TextFieldModal'
import addClickHandlers from '../../addClickHandlers'

export const EditTemplateModal = () => {
  const [templateId, setTemplateId] = useState<number | null>(null)
  const [loading, setLoading] = useState(false)
  const [input, setInput] = useState('')
  const [hasError, setHasError] = useState(false)
  const [show, setShow] = useState(false)
  addClickHandlers({
    buttonClassName: 'wpbb-edit-template-button',
    onButtonClick: (b) => {
      setInput(b.dataset.templateName)
      setTemplateId(parseInt(b.dataset.templateId))
      setShow(true)
    },
  })
  const onEditTemplate = () => {
    if (!input) {
      setHasError(true)
      return
    }
    setLoading(true)
    bracketApi
      .updateTemplate(templateId, {
        title: input,
      })
      .then((res) => {
        window.location.reload()
      })
      .catch((err) => {
        console.error(err)
        setLoading(false)
      })
  }
  return (
    <TextFieldModal
      submitButtonText={'Save'}
      onSubmit={onEditTemplate}
      header={'Edit info'}
      input={input}
      setInput={setInput}
      hasError={hasError}
      setHasError={setHasError}
      loading={loading}
      errorText={'Template name is required'}
      placeholderText={'Template name...'}
      setShow={setShow}
      show={show}
    />
  )
}
