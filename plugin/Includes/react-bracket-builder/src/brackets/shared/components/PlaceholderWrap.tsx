export interface PlaceholderWrapperProps {
  children: React.ReactNode
  extraClass?: string
}

export const PlaceholderWrapper = (props: PlaceholderWrapperProps) => {
  const { children, extraClass } = props
  return (
    <div className="tw-absolute tw-top-1/2 tw-left-1/2 tw--translate-x-1/2 tw--translate-y-1/2 tw-pointer-events-none">
      {children}
    </div>
  )
}
